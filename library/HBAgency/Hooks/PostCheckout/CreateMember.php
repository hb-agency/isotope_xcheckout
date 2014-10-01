<?php

/**
 * Copyright (C) 2014 HB Agency
 * 
 * @author		Blair Winans <bwinans@hbagency.com>
 * @author		Adam Fisher <afisher@hbagency.com>
 * @link		http://www.hbagency.com
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace HBAgency\Hooks\PostCheckout;

use Isotope\Model\Config;

class CreateMember extends \Frontend
{
	
	/**
	 * Create a member for each order if the user entered a username/password, or it's set in the config
	 * 
	 * Namespace:	Isotope\Model\ProductCollection
	 * Class:		Order
	 * Method:		checkout
	 * Hook:		$GLOBALS['ISO_HOOKS']['postCheckout']
	 *
	 * @access		public
	 * @param		object
	 * @param		array
	 * @return		void
	 */
	public function run(&$objOrder, $arrTokens)
	{
		if (FE_USER_LOGGED_IN === true)
		{
			return;
		}
		
		global $objPage;
		
		$objConfig = Config::findByRootPageOrFallback($objPage->rootId);
		
		if (!$objConfig->createMember)
		{
			return;
		}
		
		// todo: add logic for "guest" and "product" too
		if ($objConfig->createMember == 'always')
		{
			$objMember = new \MemberModel();
			$arrBillingAddress = $objOrder->getBillingAddress()->row();
			$strUsername = strval($arrBillingAddress['email']);
			
			// See if the user entered a username/password
			if ($_SESSION['CREATE_MEMBER']['username'] && $_SESSION['CREATE_MEMBER']['password'])
			{
				$objMember->username = $strUsername = $_SESSION['CREATE_MEMBER']['username'];
				$objMember->password = $_SESSION['CREATE_MEMBER']['password']; // This has already been encrypted
				
				unset($_SESSION['CREATE_MEMBER']['username']);
				unset($_SESSION['CREATE_MEMBER']['password']);
			}
			else
			{
				//Check whether the username/email exists
				$m = \MemberModel::getTable();
                $objUnique = \MemberModel::findOneBy(array("LCASE($m.username)=LCASE(?) OR LCASE($m.email)=LCASE(?)"), array($strUsername, $strUsername));
                $strUsername = $objUnique === null ? $strUsername : 'user'.rand(1000, 9999).$strUsername;
                
				$objMember->username = $strUsername;
				$objMember->password = \Encryption::encrypt($this->createRandomPassword());
			}
			
			$time = time();
			$objMember->tstamp				= $time;
			$objMember->dateAdded			= $time;
			$objMember->firstname			= strval($arrBillingAddress['firstname']);
			$objMember->lastname			= strval($arrBillingAddress['lastname']);
			$objMember->gender				= strval($arrBillingAddress['gender']);
			$objMember->company				= strval($arrBillingAddress['company']);
			$objMember->street 				= strval($arrBillingAddress['street_1']);
			$objMember->postal				= strval($arrBillingAddress['postal']);
			$objMember->city				= strval($arrBillingAddress['city']);
			$objMember->state				= strlen(strval($arrBillingAddress['subdivision'])) >= 4 ? substr($arrBillingAddress['subdivision'], 3) : '';
			$objMember->country				= strval($arrBillingAddress['country']);
			$objMember->phone				= strval($arrBillingAddress['phone']);
			$objMember->mobile				= strval($arrBillingAddress['mobile']);
			$objMember->fax					= strval($arrBillingAddress['fax']);
			$objMember->email				= strval($arrBillingAddress['email']);
			$objMember->groups				= serialize(deserialize($objConfig->createMember_groups, true));
			$objMember->login				= 1;
			$objMember->loginCount 			= 3;
			$objMember->save();
			
			$objOrder->member = $objMember->id;
			$objOrder->save();
		}
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
