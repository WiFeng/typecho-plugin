/*******************************************************************************
* @author WiFeng <admin@521-wf.com>
* @site http://521-wf.com/
* @licence http://521-wf.com/license.php
*******************************************************************************/

KindEditor.plugin('i_more', function(K) {
	var html = " <!--more--> ";
	var self = this, name = 'i_more';
	self.clickToolbar(name, function() {
		self.insertHtml(html);
	});
});