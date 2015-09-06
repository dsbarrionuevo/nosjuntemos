$(document).ready(function(){
    $("#lista_dias li label").click(function(){
        var $this = $(this);
        var $checkbox = $this.prev();
        $checkbox.prop("checked", !$checkbox.prop("checked"));
    });
});