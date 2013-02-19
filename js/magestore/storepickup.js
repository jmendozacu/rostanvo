var _currentPickupDate = null;

var Storepickup = Class.create();
Storepickup.prototype = {
    initialize: function(changeStoreUrl){
        
        this.changeStoreUrl = changeStoreUrl;
       
    },
	
	setUrl : function (url)
	{
		this.changeStoreUrl = url;
	},

	changeStore: function(){	
	
		if ($('shipping_date'))
			$('shipping_date').value ='';
		
		var storeId;
		
		storeId = $('store_id').value;
    
		var url = this.changeStoreUrl;
	   
		url = url + 'store_id/' + storeId;
	   
		var request = new Ajax.Request(url,{method: 'get', onFailure: ""}); 
		
		if($('storepickup-box') != null)
			$('storepickup-box').style.display = 'block';
		if($('date-box') != null)
			$('date-box').style.display = 'block';
		if($('time-box') != null)	
			$('time-box').style.display = 'block';
			
			//end all store mode
		if($('curr-store') != null)
		{
			var curr_store_id = $('curr-store').value;
			
			if($('store-info-'+ curr_store_id) != null)
			{
				$('store-info-'+ curr_store_id).style.display = 'none';
			}		
			
			if($('store-info-'+ storeId) != null)
			{
				$('store-info-'+ storeId).style.display = 'block';
				$('curr-store').value = storeId;
			}		
		}
			//end all store mode
	},
	
	/*
	getOldAddesss : function(){

		var address_id = $('shipping:address_id').value;
		var firstname = $('shipping:firstname').value;
		var lastname = $('shipping:lastname').value;
		var company = $('shipping:company').value;
		var street1 = $('shipping:street1').value;
		var street2 = $('shipping:street2').value;
		var city = $('shipping:city').value;
		var postcode = $('shipping:postcode').value;
		var region = $('shipping:region').value;
		var region_id = $('shipping:region_id').value;
		var country_id = $('shipping:country_id').value;
		var telephone = $('shipping:telephone').value;
		var fax = $('shipping:fax').value;	

		var url = this.changeStoreUrl;		
		
		url += 'address_id/'+ address_id;
		url += '/firstname/'+ firstname;
		url += '/lastname/'+ lastname;
		url += '/company/'+ company;
		url += '/street1/'+ street1;
		url += '/street2/'+ street2;
		url += '/city/'+ city;
		url += '/postcode/'+ postcode;
		url += '/region/'+ region;
		url += '/region_id/'+ region_id;
		url += '/country_id/'+ country_id;
		url += '/telephone/'+ telephone;
		url += '/fax/'+ fax;
		
		var request = new Ajax.Request(url,{method: 'get', onFailure: ""}); 		
	},
	*/
	selectStoreShipping : function(is_storepickup)
	{
		var url = this.changeStoreUrl;	
		
		if(is_storepickup == true)
			url += 'is_storepickup/1';
		else
			url += 'is_storepickup/2';
		
		var request = new Ajax.Request(url,{method: 'get', onFailure: ""}); 			
	},
	
	changeTime : function(url)
	{
		var shipping_date = $('shipping_date').value;
		var shipping_time = $('shipping_time').value;
		
		if(shipping_date == '')
		{
			alert('Please select shipping date');
			$('shipping_time').selectedIndex = 0;
			return;
		}
			
		url += 'shipping_date/' + shipping_date + '/shipping_time/' + shipping_time;

		var request = new Ajax.Request(url,{method: 'get', onFailure: ""}); 	
	},
	
	changeDate : function(url)
	{
		var shipping_date = $('shipping_date').value;
		var store_id = $('store_id').value;
		
		if(store_id == '' && shipping_date != '')
		{
			alert('Please select store');
			$('shipping_date').value ='';
			return;
		}
		
		if(store_id =='')
			return;
		
		if(! isDate(shipping_date))
		{
			alert('Please enter a valid date');
			return;
		}
		
		$('date-notation').innerHTML = '';
		
		url += 'shipping_date/' + shipping_date + '/store_id/'+ store_id ;
		
		$('time-please-wait').style.display = 'block';
		$('shipping_time').style.display = 'none';
		
		var request = new Ajax.Updater('shipping_time',url,{method: 'get',onComplete:function(){after_changedate();}, onFailure: ""}); 	
		
	}
}

function after_changedate()
{
	$('shipping_time').style.display = 'block';
	$('time-please-wait').style.display = 'none';
	checkHoliday();
}

var StoreLocation = Class.create();
StoreLocation.prototype = {
    initialize: function(url){
        this.url = url;
    },
	
	changecountry: function(url)
	{	
		var regionId = $('store_country_id').value;
		
		url += 'country_id/'+ regionId;
		
		new Ajax.Updater('store_region_id',url,{method: 'get', onFailure: ""});
	},	
	
	changeregion: function(url)
	{	
		var regionId = $('store_region_id').value;
		
		url += 'region_id/'+ regionId;
		
		new Ajax.Updater('store_city_id',url,{method: 'get', onFailure: ""});
	},
	
	changecity: function(url)
	{
		var cityId = $('store_city_id').value;
		
		url += 'city_id/'+ cityId;
		
		new Ajax.Updater('suburb_id',url,{method: 'get', onFailure: ""});
	},
	
	changesuburb: function(url)
	{
		var countryId = $('store_country_id').value;
		var regionId = $('store_region_id').value;
		var cityId = $('store_city_id').value;
		var suburbId = $('suburb_id').value;		
		
		url += 'country_id/'+ countryId;
		url += '/region_id/'+ regionId;
		url += '/city_id/'+ cityId;
		url += '/suburb_id/'+ suburbId;
		
		new Ajax.Updater('store_id',url,{method: 'get',onComplete: function(transport){loadedStore();},onFailure: ""});	
		
		$('storepickup-box').style.display = 'block';
		$('store_id').style.display = 'none';
		$('store-please-wait').style.display = 'block';
	},
	
	changesuburbPagestore: function(url)
	{
		var countryId = $('store_country_id').value;
		var regionId = $('store_region_id').value;
		var cityId = $('store_city_id').value;
		var suburbId = $('suburb_id').value;		
		
		url += 'country_id/'+ countryId;
		url += '/region_id/'+ regionId;
		url += '/city_id/'+ cityId;
		url += '/suburb_id/'+ suburbId;
		
		new Ajax.Updater('page-store',url,{method: 'get', onFailure: ""});	
	}	
	
}

	function loadedStore()
	{
		$('store_id').style.display = 'block';
		$('store-please-wait').style.display = 'none';
	}


/*
	function checkStore()
	{
		if($('store_id').options.length == 1)
		{
			$('store-notation').innerHTML = $('store_not_found_nonce').value;
		} else if($('store_id').options.length == 0){
		
			alert('Very early shipping time !');
	
			$('store_id').innerHTML = '<option value="">Select store</option>';
			
		}		
	}
*/
	function checkHoliday()
	{
		if($('shipping_time').options.length == 1)
		{
			//$('date-notation').innerHTML = $('holiday_nonce').value;
			switch ($('shipping_time').options[0].value) {
			case 'invalid_date':
				alert( 'Invalid date!' );
				$('shipping_time').innerHTML = '<option value="">Select Pickup Time</option>';
				break;
			case 'early_date_nonce':
				alert( $('early_date_nonce').value );
				$('shipping_time').innerHTML = '<option value="">Select Pickup Time</option>';
				break;
			case 'holiday_nonce':
				var comment = $('shipping_time').options[0].id;
				alert(comment.replace(/_/gi,' '));
				$('shipping_time').innerHTML = '<option value="">Select Pickup Time</option>';
				break;
			case 'store_closed':
				alert('Store will be closed on this day');
				$('shipping_time').innerHTML = '<option value="">Select Pickup Time</option>';
				break;	
			}
		}
		
	}
	
	function changeDate(url)
	{
		if($('shipping_date').value == _currentPickupDate)
			return;
			
		_currentPickupDate = $('shipping_date').value;

		var storepickup = new Storepickup(url);
		storepickup.changeDate(url);
	}

// check date valid 
var dtCh= "-";
var minYear=1900;
var maxYear=2100;

function isInteger(s){
	var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function isDate(dtStr){
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strMonth=dtStr.substring(0,pos1)
	var strDay=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
	//	alert("The date format should be : mm/dd/yyyy")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
	//	alert("Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
	//	alert("Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
	//	alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
	//	alert("Please enter a valid date")
		return false
	}
return true
}
