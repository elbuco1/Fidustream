$(document).ready(
	function()
	{
    	$("[href='"+location.pathname+"']").parents('li').addClass('active');

		$('.video-validation li a').click(function(e) {
			var active_tab_selector = $('.video-validation  li.active  a').attr('href');
			$('.video-validation li.active').removeClass('active');

			var $parent = $(this).parent();
			$parent.addClass('active');

			

			//hide displaying tab content
			$(active_tab_selector).removeClass('active');
			$(active_tab_selector).addClass('hide');

			var target_tab_selector = $(this).attr('href');
			$(target_tab_selector).removeClass('hide');
			$(target_tab_selector).addClass('active');
			e.preventDefault();
    	});
		$('.video-refactor li a').click(function(e) {
			var active_tab_selector = $('.video-refactor  li.active  a').attr('href');
			$('.video-refactor li.active').removeClass('active');

			var $parent = $(this).parent();
			$parent.addClass('active');

			

			//hide displaying tab content
			$(active_tab_selector).removeClass('active');
			$(active_tab_selector).addClass('hide');

			var target_tab_selector = $(this).attr('href');
			$(target_tab_selector).removeClass('hide');
			$(target_tab_selector).addClass('active');
			e.preventDefault();
    	});
	}
	
);
