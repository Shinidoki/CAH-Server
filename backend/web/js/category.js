$(document).ready(function(){
    $(document).on('click', '#saveCategory', function(){
    	$.post( 'create', { categoryName: $('#createCategoryName').val() })
		  .done(function( data ) {
		  	if(data.success){
		  		$('#createCategoryName').val('');
		  		$('#createCategory').modal('hide');
		  	}else{
		  		$('#createCategoryAlert').text('Die Kategorie konnte nicht angelegt werden!');
		  		$('#createCategoryAlert').slideDown('slow');
		  	}
		  });
    });
    $(document).on('hidden.bs.modal', '#createCategory', function (e) {
  		$('#createCategoryName').val('');
	})
});