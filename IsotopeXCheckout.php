<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009-2011
 * @author     Blair Winans <blair@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */



class IsotopeXCheckout extends Frontend
{

	/**
	 * Import some default libraries
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
		$this->import('Encryption');
	}


	/**
	 * NOTE - This is EXACTLY THE SAME as IsotopeMember class
	 * Trigger the correct function based on Isotope version and member login status
	 * @params mixed
	 * @return mixed
	 */
	public function triggerAction()
	{
		$this->import('Isotope');

		$blnCompatible = version_compare(ISO_VERSION, '0.3', '<');
		$arrParam = func_get_args();

		if (FE_USER_LOGGED_IN && $blnCompatible)
		{
			$this->import('FrontendUser', 'User');
			return call_user_func_array(array($this, 'assignGroupsCompatible'), $arrParam);
		}
		elseif (FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');
			return call_user_func_array(array($this, 'assignGroups'), $arrParam);
		}
		elseif ($blnCompatible)
		{
			return call_user_func_array(array($this, 'addMemberCompatible'), $arrParam);
		}
		else
		{
			return call_user_func_array(array($this, 'addMember'), $arrParam);
		}
	}


	/**
	 * Create a new member on checkout
	 * NOTE - This is EXACTLY THE SAME as IsotopeMember class EXCEPT it runs ONLY on the guest option AND we are activating the account
	 * @param object
	 * @param object
	 * @return bool
	 */
	protected function addMember($objOrder, $objCart)
	{
		// Cancel if createMember-guest is not enabled in store config
		if ($this->Isotope->Config->createMember != 'guest')
			return true;

		$intExpiration = 0;

		if ($this->Isotope->Config->createMember_expiration != '')
		{
			$intExpiration = strtotime($this->Isotope->Config->createMember_expiration);
		}

		// Prepare address. This will dynamically use all fields available in both member and address
		$arrAddress = deserialize($objOrder->billing_address, true);
		$arrData = array_intersect_key($arrAddress, array_flip($this->Database->getFieldNames('tl_member')));
		unset($arrData['id'], $arrData['pid']);

		if ($arrData['street'] == '')
		{
			$arrData['street'] = (string)$arrAddress['street_1'];
		}

		// HOOK: generate member callback
		if (isset($GLOBALS['ISO_HOOKS']['generateMember']) && is_array($GLOBALS['ISO_HOOKS']['generateMember']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateMember'] as $callback)
			{
				$this->import($callback[0]);
				$arrData = $this->$callback[0]->$callback[1]($arrData, $objOrder);
			}
		}

		if ($arrData['username'] == '')
		{
			$arrData['username'] = (string)$arrData['email'];
		}

		// Verify the user does not yet exist (especially when using email address)
		$objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE username=?")->execute($arrData['username']);
		if ($objMember->numRows)
		{
			$this->log('Could not create member for order ID '.$objOrder->id.' (username "'.$arrData['username'].'" exists).', __METHOD__, TL_ERROR);
			return true;
		}

		// Create member, based on ModuleRegistration::createMember from Contao 2.9
		$arrData['tstamp'] = time();
		$arrData['login'] = $arrData['username'] ? '1' : '';
		$arrData['activation'] = md5(uniqid(mt_rand(), true));
		$arrData['dateAdded'] = $arrData['tstamp'];
		$arrData['groups'] = serialize($this->Isotope->Config->createMember_groups);
		$arrData['newsletter'] = in_array('newsletter', $this->Config->getActiveModules()) ? deserialize($this->Isotope->Config->createMember_newsletters, true) : array();

		// Set expiration date if enabled
		if ($intExpiration > 0)
		{
			$arrData['stop'] = $intExpiration;
		}

		//******** ACTIVATE account **********
		$arrData['disable'] = 0;
		//******** ACTIVATE account **********
		
		
		//******** SET PASSWORD**********
		if(isset($_SESSION['CREATE_MEMBER']))
		{
			$arrData['password'] = $this->Encryption->decrypt($_SESSION['CREATE_MEMBER']);
			unset($_SESSION['CREATE_MEMBER']);
		}
		else
		{
			$arrData['password_raw'] = $this->createRandomPassword();
			$strSalt = substr(md5(uniqid(mt_rand(), true)), 0, 23);
			$arrData['password'] = sha1($strSalt . $arrData['password_raw']) . ':' . $strSalt;
		}
		//******** SET PASSWORD**********
		
		$arrSet = $arrData;
		if (!$this->Database->fieldExists('password_raw', 'tl_member'))
		{
			unset($arrSet['password_raw']);
		}

		// Create user
		$objNewUser = $this->Database->prepare("INSERT INTO tl_member %s")->set($arrSet)->execute();
		$insertId = $objNewUser->insertId;

		// Assign home directory
		if ($this->Isotope->Config->createMember_assignDir && is_dir(TL_ROOT . '/' . $this->Isotope->Config->createMember_homeDir))
		{
			$this->import('Files');
			$strUserDir = strlen($arrData['username']) ? $arrData['username'] : 'user_' . $insertId;

			// Add the user ID if the directory exists
			if (is_dir(TL_ROOT . '/' . $this->Isotope->Config->createMember_homeDir . '/' . $strUserDir))
			{
				$strUserDir .= '_' . $insertId;
			}

			new Folder($this->Isotope->Config->createMember_homeDir . '/' . $strUserDir);

			$this->Database->prepare("UPDATE tl_member SET homeDir=?, assignDir=1 WHERE id=?")
						   ->execute($this->Isotope->Config->createMember_homeDir . '/' . $strUserDir, $insertId);
		}

		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser']))
		{
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($insertId, $arrData);
			}
		}

		$arrData['domain'] = $this->Environment->host;

		// Generate activation link
		global $objPage;
		$objJump = $this->Database->execute("SELECT * FROM tl_page WHERE id=(SELECT iso_activateAccount FROM tl_page WHERE id={$objPage->rootId})");
		if ($objJump->numRows)
		{
			$strUrl = $this->generateFrontendUrl($objJump->fetchAssoc());
			$strUrl = strpos($strUrl, 'http') === 0 ? $strUrl : $this->Environment->base . $strUrl;
			$arrData['link'] = $strUrl . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strUrl, '?') !== false) ? '&' : '?') . 'token=' . $arrData['activation'];
		}

		// Support newsletter extension
		if (count($arrData['newsletter']) > 0)
		{
			$objChannels = $this->Database->execute("SELECT title FROM tl_newsletter_channel WHERE id IN(". implode(',', array_map('intval', $arrData['newsletter'])) .")");
			$arrData['channel'] = $arrData['channels'] = implode("\n", $objChannels->fetchEach('title'));
		}
		unset($arrData['newsletter']);

		// Format data for email
		foreach( $arrData as $field => $value )
		{
			$arrData[$field] = $this->Isotope->formatValue('tl_member', $field, $value);
		}

		// Send activation e-mail
		if ($this->Isotope->Config->createMember_mail && $objOrder->iso_customer_email)
		{
			$this->Isotope->sendMail($this->Isotope->Config->createMember_mail, $objOrder->iso_customer_email, $GLOBALS['TL_LANGUAGE'], $arrData);
		}

		// Inform admin if no activation link is sent
		if ($this->Isotope->Config->createMember_adminMail && $objOrder->iso_sales_email)
		{
			$this->Isotope->sendMail($this->Isotope->Config->createMember_adminMail, $objOrder->iso_sales_email, $GLOBALS['TL_LANGUAGE'], $arrData);
		}

		// Assign the current order to the new member
		if (version_compare(ISO_VERSION, '0.3', '<'))
		{
			$this->Database->prepare("UPDATE tl_iso_orders SET pid=? WHERE id=?")->executeUncached($insertId, $objOrder->id);
		}
		else
		{
			$objOrder->pid = $insertId;
			$objOrder->save();
		}

		return true;
	}


	/**
	 * Assign current member to the product groups
	 *
	 * @param	object
	 * @param	object
	 * @return	bool
	 */
	protected function assignGroups($objOrder, $objCart)
	{
		$arrGroups = array();
		$intExpiration = 0;
		
		// Search for products with expiration date
		foreach( $objCart->getProducts() as $objProduct )
		{
			if (is_array($objProduct->assignMemberGroups))
			{
				$arrGroups = array_merge($arrGroups, $objProduct->assignMemberGroups);
			}
			
			if ($objProduct->memberExpiration != '')
			{
				if ($intExpiration == 0)
				{
					$intExpiration = $this->User->stop > 0 ? $this->User->stop : time();
				}
				
				$intExpiration = strtotime($objProduct->memberExpiration, $intExpiration);
			}
		}
		
		if ($intExpiration > 0)
		{
			$this->Database->prepare("UPDATE tl_member SET stop=? WHERE id=?")->executeUncached($intExpiration, $this->User->id);
			$this->User->stop = $intExpiration;
		}
		
		if (count($arrGroups))
		{
			$arrGroups = array_merge((array)$this->User->groups, array_unique($arrGroups));
			
			$this->Database->prepare("UPDATE tl_member SET groups=? WHERE id=?")->executeUncached(serialize($arrGroups), $this->User->id);
			$this->User->groups = $arrGroups;
		}
		
		return true;
	}


	/**
	 * Backward-compatible function for Isotope 0.2
	 */
	protected function addMemberCompatible($orderId, $blnCheckout, $objModule)
	{
		if (!$blnCheckout || FE_USER_LOGGED_IN || !$this->Isotope->Config->createMember)
			return $blnCheckout;

		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('id', $orderId))
		{
			$this->log('Could not create member for order ID '.$orderId.' (Order not found).', __METHOD__, TL_ERROR);
			return $blnCheckout;
		}

		$strCustomerName = '';
		$strCustomerEmail = '';

		if ($objOrder->billingAddress['email'] != '')
		{
			$strCustomerName = $objOrder->billingAddress['firstname'] . ' ' . $objOrder->billingAddress['lastname'];
			$strCustomerEmail = $objOrder->billingAddress['email'];
		}
		elseif ($objOrder->shippingAddress['email'] != '')
		{
			$strCustomerName = $objOrder->shippingAddress['firstname'] . ' ' . $objOrder->shippingAddress['lastname'];
			$strCustomerEmail = $objOrder->shippingAddress['email'];
		}

		if (trim($strCustomerName) != '')
		{
			$strCustomerEmail = sprintf('%s <%s>', $strCustomerName, $strCustomerEmail);
		}

		if ($strCustomerEmail != '')
		{
			$objOrder->iso_customer_email = $strCustomerEmail;
		}

		$objOrder->iso_sales_email = $GLOBALS['TL_ADMIN_NAME'] != '' ? sprintf('%s <%s>', $GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) : $GLOBALS['TL_ADMIN_EMAIL'];

		return $this->addMember($objOrder, $this->Isotope->Cart);
	}


	/**
	 * Backward-compatible function for Isotope 0.2
	 */
	protected function assignGroupsCompatible($orderId, $blnCheckout, $objModule)
	{
		$objOrder = new IsotopeOrder();
		
		if ($objOrder->findBy('id', $orderId))
		{
			$this->assignGroups($objOrder, $this->Isotope->Cart);
		}
		
		return $blnCheckout;
	}


	/**
	 * Generate a random password with 8 characters
	 *
	 * The letter l (lowercase L) and the number 1 have been removed,
	 * as they can be mistaken for each other.
	 *
	 * @param	void
	 * @return	string
	 * @link	http://www.totallyphp.co.uk/code/create_a_random_password.htm
	 */
	private function createRandomPassword()
	{
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    $i = 0;
	    $pass = '' ;

	    while ($i <= 7) {
	        $num = rand() % 33;
	        $tmp = substr($chars, $num, 1);
	        $pass = $pass . $tmp;
	        $i++;
	    }

	    return $pass;
	}


}
