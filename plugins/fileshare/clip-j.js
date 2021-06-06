function clip(text) {
	if (!('remove' in Element.prototype)) {
		Element.prototype['remove'] = function () {
			if (this.parentNode) {
				this.parentNode.removeChild(this);
			}
		};
	}
	var copyElement = document.createElement('input');
	copyElement.setAttribute('type', 'text');
	copyElement.setAttribute('value', text);
	copyElement = document.body.appendChild(copyElement);
	copyElement.select();
	var copyCommand = document.execCommand('copy');
	try {
		if(!copyCommand) throw 'Not found.';
	} catch(e) {
		copyElement.remove();
		prompt(theUILang.FScopylinkmsg, text);
	} finally {
		if (typeof e == 'undefined') {
			if(window.clipboardData && window.clipboardData.getData) {
				var pasteContent = window.clipboardData.getData('Text');
				var pasteElement = document.createElement('input');
				pasteElement.setAttribute('type', 'text');
				pasteElement.setAttribute('value', pasteContent);
				pasteElement = document.body.appendChild(pasteElement);
				copyCommand = copyElement.value === pasteElement.value;
				copyElement.remove();
				pasteElement.remove();
				if(!copyCommand) {
					prompt(theUILang.FScopylinkmsg, text);
				}
			} else {
				copyElement.remove();
			}
		}
	}
}
