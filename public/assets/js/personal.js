$(function() {

		$(".voca-select li").click(function() {

			if ($(this).attr('class') != "active") {
			$(this).parent().find("li.active").attr('class', '');
			$(this).attr('class', 'active');
			}

			var id = $(this).attr('id');
			$("#which-vocabulary").attr('value', id);
			$("#show-message").html("");

			});
		

		$("#save-vocabulary-btn").click(function(){
				var id = $("#which-vocabulary").val();
				
				var data = {'id':id}

				$.post("/rsconfig", data, function(rsp){
						var obj = $("#show-message");
						if (rsp.code == 0) {
							obj.html("Save success.");
						}
						else
						{
							obj.html("Save failed.");
						}
					}, 'json');
			
			});

});

