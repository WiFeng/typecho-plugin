/*******************************************************************************
* @author WiFeng <admin@521-wf.com>
* @site http://521-wf.com/
* @licence http://521-wf.com/license.php
*******************************************************************************/

KindEditor.plugin('i_br', function(K) {
	var html = "<br /> ";
	var self = this, name = 'i_br';
	self.clickToolbar(name, function() {
		self.insertHtml(html);
	});
});