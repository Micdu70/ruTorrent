plugin.loadLang();

var pluginActionPath = "plugins/addzip/action.php"

var makeAddRequestZip = function(frm)
{
    var s = pluginActionPath+"?";
    if($("#torrents_start_stopped").attr("checked"))
        s += 'torrents_start_stopped=1&';
    if($("#fast_resume").attr("checked"))
        s += 'fast_resume=1&';
    if($("#not_add_path").attr("checked"))
        s += 'not_add_path=1&';
    var dir = $.trim($("#dir_edit").val());
    if(dir.length)
        s += ('dir_edit='+encodeURIComponent(dir)+'&');
    var lbl = $.trim($("#tadd_label").val());
    if(lbl.length)
        s += ('label='+encodeURIComponent(lbl));
    frm.action = s;
    return(true);
}

plugin.onLangLoaded = function()
{
	$(document.body).append($("<iframe name='uploadfrmzip'/>").css({visibility: "hidden"}).attr( { name: "uploadfrmzip" } ).width(0).height(0).on('load', function()
	{
		$("#torrent_zip").val("");
		$("#add_zip").attr("disabled",false);
		var d = (this.contentDocument || this.contentWindow.document);
		if(d.location.href != "about:blank")
			try { eval(d.body.innerHTML); } catch(e) {}
	}));

    $('#tadd').css('height', 'auto');
    var addtorrentdiv = $('#tadd').find("div.cont.fxcaret");
    addtorrentdiv.append("<hr>");

    addtorrentdiv.append('<form target="uploadfrmzip" method="post" enctype="multipart/form-data" id="addtorrentzip" action="">' +
                            '<label>Torrent ZIP:</label>' +
                            '<input id="torrent_zip" type="file" class="TextboxLarge" name="torrent_zip">' +
                            '<br>' +
                            '<label>&nbsp;</label>' +
                            '<input type="submit" class="Button" value="' + theUILang.addZip + '" id="add_zip">' +
                        '</form>');

    $('#addtorrentzip').submit(
        function() {
            if(!$("#torrent_zip").val().match(".zip"))
            {
                alert(theUILang.Not_torrent_file);
                return(false);
            }
            $("#add_zip").attr("disabled",true);
            return(makeAddRequestZip(this));
        }
    );
}

plugin.onRemove = function()
{
	$('#uploadfrmzip').remove();
	$('#addtorrentzip').remove();
}
