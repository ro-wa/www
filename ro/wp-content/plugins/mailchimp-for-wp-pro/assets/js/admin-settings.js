(function($) {

	$context = $('#mc4wp-admin');

	$context.find('input[name$="[double_optin]"]').change(function() {
		if($(this).val() == 0) {
			$context.find("#mc4wp-send-welcome").removeClass('hidden').find(':input').removeAttr('disabled');
		} else {
			$context.find("#mc4wp-send-welcome").addClass('hidden').find(':input').attr('disabled', 'disabled').attr('checked', false);
		}
	});

	$context.find('input[name$="[show_at_woocommerce_checkout]"]').change(function() {
		$context.find('tr#woocommerce-settings').toggle( $(this).prop( 'checked') );
	});


	$context.find('input[name="mc4wp_form[update_existing]"]').change(function() {
		if($(this).val() == 1) {
			$context.find("#mc4wp-replace-interests").removeClass('hidden').find(':input').removeAttr('disabled');
		} else {
			$context.find("#mc4wp-replace-interests").addClass('hidden').find(':input').attr('disabled', 'disabled').attr('checked', false);
		}
	});

	$context.find("select[name='mc4wp_form[css]']").change(function() {
		$context.find("#mc4wp-custom-color").toggle(($(this).val() == 'custom-color'));
	});

	// init
	$context.find('input.color-field').wpColorPicker();

})(jQuery);