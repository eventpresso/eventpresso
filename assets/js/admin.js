'use strict';

var eventpresso_metabox = {
	init: function init() {

		if (jQuery('.eventpresso-metabox-container').length > 0) {
			this.container = jQuery('.eventpresso-metabox-container');
			this.fields = jQuery('.eventpresso-fields-container');
			this.init_tabs();
			this.init_date();
		}
	},
	init_date: function init_date() {
		if (jQuery('.eventpresso-field-container-date').length > 0) {
			jQuery('.eventpresso-field-container-date').each(function () {
				var $input = jQuery(this).find('input');
				var options = $input.data('options');
				$input.datepicker(options);
			});
		}
	},
	init_tabs: function init_tabs() {
		var self = this;
		if (this.container.hasClass('eventpresso-metabox-has-tabs')) {

			this.tabs = jQuery('.eventpresso-tabs-container');
			this.fields.find('.eventpresso-field-container').addClass('eventpresso-field-hidden');

			this.tabs.on('click', '.eventpresso-tab-container', function (e) {
				e.preventDefault();
				var id = jQuery(this).data('tab');
				jQuery('.eventpresso-tab-container').removeClass('eventpresso-tab-active');
				jQuery(this).addClass('eventpresso-tab-active');
				self.fields.find('.eventpresso-field-container').addClass('eventpresso-field-hidden').removeClass('eventpresso-field-visible');
				self.fields.find('[data-tab="' + id + '"]').addClass('eventpresso-field-visible');
			});

			this.tabs.find('.eventpresso-tab-container').first().click();
		}
	}
};

jQuery(document).ready(function ($) {
	eventpresso_metabox.init();
});
