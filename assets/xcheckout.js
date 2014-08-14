/**
 * Isotope XCheckout for Contao Open Source CMS
 *
 * Copyright (C) 2014 HB Agency
 *
 * @package    XCheckout
 * @link       http://www.hbagency.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


var IsotopeXCheckout = (function() {
    "use strict";

    var loadMessage = 'Loading checkout data...';
    var spinner = null;
    var step, 
    	form,
    	billingadd,
    	shippingadd,
    	orderinfo, 
		shippingmethod,
		paymentmethod,
		buttons,
		buttonContainer,
		isSending,
		url,
		xhr;
		
    function initCheckout(config) {
		
		isSending = false;
		step = config.step;
		form = document.getElementById('iso_mod_checkout_'+step);
		
		//Find addresses, methods & review
		billingadd 			= document.getElementById(step+'_billingaddress');
		shippingadd 		= document.getElementById(step+'_shippingaddress');
		shippingmethod 		= document.getElementById(step+'_shippingmethod');
		paymentmethod 		= document.getElementById(step+'_paymentmethod');
		orderinfo 			= document.getElementById(step+'_orderinfo');
		
		//Disable buttons for now
		buttons = document.getElementsByTagName('input');
		
		toggleButtons(false);

        if (form) {
            setXHR(form, config, handleResponse);
            sendAndRefresh();
        }
        
        setEvents();
        
    }
    
    function setEvents() {
		if(billingadd){ 	setInputsandSelects(billingadd, true);}
		if(shippingadd){ 	setInputsandSelects(shippingadd, true);}
		if(shippingmethod){ setInputsandSelects(shippingmethod, false);}
		if(paymentmethod){ 	setInputsandSelects(paymentmethod, false);}
		
		if (buttons.length) {
			for ( var i in buttons) {
	        	if( (' ' + buttons[i].className + ' ').indexOf(' button ') > -1 &&
	        		(' ' + buttons[i].className + ' ').indexOf(' next ') > -1 &&
	        		(' ' + buttons[i].className + ' ').indexOf(' submit ') > -1) {
	        		
					try{
					  buttons[i].parentNode.addEventListener('mouseover', sendAndRefresh );
					} catch (err){
					  buttons[i].parentNode.attachEvent('onmouseover', sendAndRefresh );
					}
				}
			}
		}
	}
	
	function setInputsandSelects(field, runAllInputs) {
		
		var inputs = field.getElementsByTagName('input'), i, j, k;
		if (inputs.length) {
			for (i in inputs) {
	        	if((' ' + inputs[i].type + ' ').indexOf(' radio ') > -1) {
					inputs[i].addEventListener('click', sendAndRefresh );
				}
			}
		}
		
		if (runAllInputs) {
			if (inputs.length) {
				for (j in inputs) {
		        	if((' ' + inputs[j].type + ' ').indexOf(' text ') > -1) {	
						try{
						  inputs[j].addEventListener('blur', sendAndRefresh );
						} catch (err){
						  inputs[j].attachEvent('onblur', sendAndRefresh );
						}
						//inputs[j].addEventListener('blur', sendAndRefresh );
					}
		        	if((' ' + inputs[j].type + ' ').indexOf(' email ') > -1) {
						try{
						  inputs[j].addEventListener('blur', sendAndRefresh );
						} catch (err){
						  inputs[j].attachEvent('onblur', sendAndRefresh );
						}
						//inputs[j].addEventListener('blur', sendAndRefresh );
					}
		        	if((' ' + inputs[j].type + ' ').indexOf(' tel ') > -1) {
						try{
						  inputs[j].addEventListener('blur', sendAndRefresh );
						} catch (err){
						  inputs[j].attachEvent('onblur', sendAndRefresh );
						}
						//inputs[j].addEventListener('blur', sendAndRefresh );
					}
				}
			}
			
			var selects = field.getElementsByTagName('select');
			if (selects.length) {
				for (k in selects) {
					if((' ' + selects[k].className + ' ').indexOf(' select ') > -1) {
						try{
						  selects[k].addEventListener('change', sendAndRefresh );
						} catch (err){
						  selects[k].attachEvent('onchange', sendAndRefresh );
						}
			        	//selects[k].addEventListener('change', sendAndRefresh );
			        }
				}
			}
		}
	}
	
	
	function sendAndRefresh()
	{
		var doRequest = true;
		
		doRequest = billingadd ? checkMandatoryFields(billingadd, true) : doRequest;
		doRequest = shippingadd ? checkMandatoryFields(shippingadd, true) : doRequest;
		
		if (!doRequest || isSending) return;
		
		isSending = true;
	    xhr.open("POST", url, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		form = document.getElementById('iso_mod_checkout_'+step); // "form" was not sending the correct values
        xhr.send(form.toQueryString());
	}
	
	
	function checkMandatoryFields(field, runAllInputs)
	{
		var valid = true, j, k;
				
		return valid;
	}


    function setXHR(form, config, callback) {
        var i, el;
        var spinnerParent = shippingmethod ? shippingmethod : (billingadd ? billingadd : null);

	    try {
	        xhr = new XMLHttpRequest();
	    } catch (e) {
	        try { 
	            xhr = new ActiveXObject("Msxml2.XMLHTTP");
	        } catch (e) {
	            try {
	                xhr = new ActiveXObject("Microsoft.XMLHTTP");
	            } catch (e){
	                return null;
	            }
	        }
	    }
	    
	    xhr.onreadystatechange = function() {
	    	if (xhr.readyState == 1) {
		    	//Add a spinner
				if(spinnerParent){
					try{
						spinnerParent.removeChild(spinner);
					}catch(err){}
					spinner = null;
					spinner = document.createElement('div');
					spinner.setAttribute('id', 'spinner');
					spinnerParent.appendChild(spinner);
				}
	    	}
	        if (xhr.readyState == 4) {   
	           isSending = false;
	           //Check for redirect and simply enable buttons
	           try {
	            //console.log(xhr.status);
	            if(xhr.status == 204 || xhr.status == 1223) { // IE needs 1223 for some stupid reason
				 try{
				 	spinnerParent.removeChild(spinner);
				 }catch(err){}
	           	 spinner = null;
	           	 toggleButtons(true);
	           	 return;
	            }
	           
	            if(xhr.responseText) {
	             handleResponse(xhr.responseText);
	            }
				try{
					spinnerParent.removeChild(spinner);
				}catch(err){}
	            spinner = null;
	           
	           }catch(err){}
	        }
	    };

		url = 'ajax/?mod=xcheckout&action=fmd&page='+config.page+'&id='+config.module+'&step='+config.step;

    }
    
    
    function handleResponse(responseText) {
				
	    var json = JSON.parse(responseText);
	    
	    //Replace request tokens
	    var inputs = document.getElementsByTagName('input'), i;
	    if (inputs.length) {
			for (i in inputs) {
	        	if((' ' + inputs[i].name + ' ').indexOf(' REQUEST_TOKEN ') > -1) {
					inputs[i].setAttribute('value', json.token);
				}
			}
		}
		
		var steps = json.content.steps, j;
	    
	    if (steps.length) {
			for (j in steps) {
	        	if((' ' + steps[j].id + ' ').indexOf(' '+step+'_shippingmethod ') > -1 && shippingmethod) {
					shippingmethod.innerHTML = steps[j].html;
					var ptags = shippingmethod.getElementsByTagName('p');
					for (var i = 0; i < ptags.length; i++)
					{
						if (ptags[i].textContent.indexOf('Invalid') > -1)
						{
							ptags[i].textContent = 'Please select.';
						}
					}
				}
				else if((' ' + steps[j].id + ' ').indexOf(' '+step+'_paymentmethod ') > -1 && paymentmethod) {
					paymentmethod.innerHTML = steps[j].html;
				}
				else if((' ' + steps[j].id + ' ').indexOf(' '+step+'_orderinfo ') > -1 && orderinfo) {
					orderinfo.innerHTML = steps[j].html;
				}
			}
		}
		
		//Check for disabled buttons
		toggleButtons(json.content.lock != 1);
		
		//Refresh events
		setEvents();
	    
    }
    
    function toggleButtons(toggleOn) {

		if (buttons.length) {
			for ( var i in buttons) {
	        	if( (' ' + buttons[i].className + ' ').indexOf(' button ') > -1 &&
	        		(' ' + buttons[i].className + ' ').indexOf(' next ') > -1 &&
	        		(' ' + buttons[i].className + ' ').indexOf(' submit ') > -1) {
	        		
	        		if(toggleOn !== false && toggleOn != null) {
		        		buttons[i].removeAttribute('disabled');
	        		}
	        		else if ((' ' + document.body.className + ' ').indexOf(' ie8 ') == -1) {
		        		buttons[i].setAttribute('disabled', 'disabled');
	        		}
				}
			}
		}
    }

    return {
        'attach': function(checkoutConfig) {
            initCheckout(checkoutConfig);
        },

        /**
         * Overwrite the default message
         */
        'setLoadMessage': function(message) {
            loadMessage = message || 'Loading checkout data...';
        }
    };
})();