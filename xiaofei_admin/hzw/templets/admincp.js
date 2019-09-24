var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser','rv':'rv'});
if(BROWSER.safari || BROWSER.rv) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
	HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s|:)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function $(id) {
	return !id ? null : document.getElementById(id);
}
function altStyle(obj, disabled) {
	function altStyleClear(obj) {
		var input, lis, i;
		lis = obj.parentNode.getElementsByTagName('li');
		for(i=0; i < lis.length; i++){
			lis[i].className = '';
		}
	}
	var disabled = !disabled ? 0 : disabled;
	if(disabled) {
		return;
	}

	var input, lis, i, cc, o;
	cc = 0;
	lis = obj.getElementsByTagName('li');
	for(i=0; i < lis.length; i++){
		lis[i].onclick = function(e) {
			o = BROWSER.ie ? event.srcElement.tagName : e.target.tagName;
			altKey = BROWSER.ie ? window.event.altKey : e.altKey;
			if(cc) {
				return;
			}
			cc = 1;
			input = this.getElementsByTagName('input')[0];
			if(input.getAttribute('type') == 'checkbox' || input.getAttribute('type') == 'radio') {
				if(input.getAttribute('type') == 'radio') {
					altStyleClear(this);
				}

				if(BROWSER.ie || o != 'INPUT' && input.onclick) {
					input.click();
				}
				if(this.className != 'checked') {
					this.className = 'checked';
					input.checked = true;
				} else {
					this.className = '';
					input.checked = false;
				}
				if(altKey && input.name.match(/^multinew\[\d+\]/)) {
					miid = input.id.split('|');
					mi = 0;
					while($(miid[0] + '|' + mi)) {
						$(miid[0] + '|' + mi).checked = input.checked;
						if(input.getAttribute('type') == 'radio') {
							altStyleClear($(miid[0] + '|' + mi).parentNode);
						}
						$(miid[0] + '|' + mi).parentNode.className = input.checked ? 'checked' : '';
						mi++;
					}
				}
			}
		};
		lis[i].onmouseup = function(e) {
			cc = 0;
		}
	}
}

function setfaq(obj, id) {
	if(!$(id)) {
		return;
	}
	$(id).style.display = '';
	if(!obj.onmouseout) {
		obj.onmouseout = function () {
			$(id).style.display = 'none';
		}
	}
}

function checkAll(type, form, value, checkall, changestyle) {
	var checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(type == 'option' && e.type == 'radio' && e.value == value && e.disabled != true) {
			e.checked = true;
		} else if(type == 'value' && e.type == 'checkbox' && e.getAttribute('chkvalue') == value) {
			e.checked = form.elements[checkall].checked;
			if(changestyle) {
				multiupdate(e);
			}
		} else if(type == 'prefix' && e.name && e.name != checkall && (!value || (value && e.name.match(value)))) {
			e.checked = form.elements[checkall].checked;
			if(changestyle) {
				if(e.parentNode && e.parentNode.tagName.toLowerCase() == 'li') {
					e.parentNode.className = e.checked ? 'checked' : '';
				}
				if(e.parentNode.parentNode && e.parentNode.parentNode.tagName.toLowerCase() == 'div') {
					e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
				}
			}
		}
	}
}
