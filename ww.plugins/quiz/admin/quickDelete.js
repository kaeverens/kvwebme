function deleteItem () {
  	var questionid= $(this)
		.closest("li")
		.attr("id");
	this.id= "delete_link_"+questionid;
	if (
		confirm("Are you sure you want to delete this question?")
			){
			$.getJSON (
				'/ww.plugins/quiz/admin/question-delete.php?questionid='+questionid,
				remove_row
			);
		}
		return false;
}
function remove_row (data) {
  	if(!data.status){
		return alert("Could not delete");
	}
	$("#delete_link_"+data.id)
	.closest("li")
	.remove();
}
