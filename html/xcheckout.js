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


var IsotopeXcheckout = new Class(
{
	Implements: Options,
	sender: null,
	options: {
		language: 'en',
		loadMessage: 'Loading checkout data...',
		page: 0,
		mode: 'FE'
	},
	
	initialize: function(module, step, options)
	{
		this.setOptions(options);
		this.module = module;
		this.step = step;
		this.container = document.id('iso_mod_checkout_'+this.step);
		
		//Find methods
		var shipmethod = this.container.getElement('div.shipping_method');
		this.shippingmethod = shipmethod ? shipmethod : false;
		var paymethod = this.container.getElement('div.payment_method');
		this.paymentmethod = paymethod ? paymethod : false;
				
		//Find address fields
		var billadd = this.container.getElement('#billing_address');
		this.billingadd = billadd ? billadd : false;
		var shipadd = this.container.getElement('#shipping_address');
		this.shippingadd = shipadd ? shipadd : false;
				
		//Set initial events
		this.setSender();
		this.setEvents();
						
	},
	
	setEvents: function()
	{
		//Billing fields
		if(this.billingadd){this.setInputsandSelects(this.billingadd);}
		
		//Shipping fields
		if(this.shippingadd){this.setInputsandSelects(this.shippingadd);}
		
		//Shipping method
		if(this.shippingmethod){this.setInputsandSelects(this.shippingmethod);}
		
		//Payment method
		if(this.paymentmethod){this.setInputsandSelects(this.paymentmethod);}
	},
	
	setInputsandSelects: function(field)
	{
		var inputs = field.getElements('input[type="text"]');
		inputs.each(function(input){
			input.addEvent('blur', function(){ this.sendAndRefresh(); }.bind(this));
		}.bind(this));
		var radios = field.getElements('input[type="radio"]');
		radios.each(function(radio){
			radio.addEvent('click', function(){ this.sendAndRefresh(); }.bind(this));
		}.bind(this));
		var selects = field.getElements('select');
		selects.each(function(select){
			select.addEvent('change', function(){ this.sendAndRefresh(); }.bind(this));
		}.bind(this));
	},
	
	setSender: function()
	{
		this.container.set('send', 
		{
			url: 'ajax.php?action=fmd&page='+this.options.page+'&id='+this.module+'&step='+this.step,
			link: 'cancel',
			onSuccess: function(txt, xml)
			{
				var json = JSON.decode(txt);
				// Update request token
				REQUEST_TOKEN = json.token;
				document.getElements('input[type="hidden"][name="REQUEST_TOKEN"]').set('value', json.token);
				
				this.fadeIn(json.content);
				
			}.bind(this),
			onFailure: function(){}.bind(this)
		});
		
	},
	
	sendAndRefresh: function()
	{
		this.container.send();
	},
	
	fadeIn: function(response)
	{
		var lock = response.lock;
		var methods = response.methods;
				
		methods.each( function(method)
		{
			if(method.type=='shipping_method' && this.shippingmethod)
			{
				this.shippingmethod.set('html', method.html);
			}
			else if(method.type=='payment_method' && this.paymentmethod)
			{
				this.paymentmethod.set('html', method.html);;
			}
		}.bind(this));
		
		//Unlock the jump to next step
		if(lock!='1')
		{
			var buttons = this.container.getElements('input.submit.next');
			buttons.each(function(btn){ btn.removeProperty('disabled'); });
		}
		else
		{
			var buttons = this.container.getElements('input.submit.next');
			buttons.each(function(btn){ btn.setProperty('disabled', 'disabled'); });
		}
		
	}
	
});
