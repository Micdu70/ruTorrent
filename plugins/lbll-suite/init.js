//
// ruTorrent Torrent-Addition Auto-Labels
// Version 0.8
// by thezwallrus
//

//
// jquery replace
//

$("#tadd_label").wrap('<div id="taddl_cont" />').remove();
$("#taddl_cont").append('<select id="tadd_label" name="tadd_label" style="width:120px;margin-left:4px;"></select>'+
			'<input type="text" id="newLabel" value="'+ theUILang.New_label +'" title="'+ theUILang.New_label +'" style="width:90px;"/>'+
			'<input type="button" id="add_newLabel" value="+" class="Button" style="width:25px;" />');
$("#tadd_label").append($('<OPTION></OPTION>')
		.val('')
		.text(''));

$('#addtorrent label').css({'margin-top':'4px'});
$('#addtorrent label:eq(4)').css({'margin-top':'10px'});
$('#addtorrenturl label').css({'margin-top':'4px'});

//
// jQuery listeners
//

var addLab = $('#add_newLabel');
addLab.click( function() {
	setTimeout( function() {
		$('#tadd_label')
		.append($('<OPTION></OPTION>')
		.val( $('#newLabel').val() )
		.text( $('#newLabel').val() )
		.attr('selected','selected') );
		$('#newLabel').val( $('#newLabel').attr('title') );
	}, 300)
});



var newLab = $('#newLabel');

newLab.focus( function() {
	if(this.value == $(this).attr('title')) {
		this.value='';
	}
});

newLab.blur( function() {
	if(this.value == '') {
		this.value = $(this).attr('title');
	}
});

//
// load labels into dropdown
//

theWebUI.initLabelDirs = function()
{
		setTimeout( function() {
			jQuery.each(theWebUI.cLabels, function(lbl, nothing) {
				$('#tadd_label').append($('<OPTION></OPTION>').val(lbl).html(lbl));
			})
		}, 3000 );
		plugin.markLoaded();
};

theWebUI.initLabelDirs();


function labelTree(branches) {
  const roots = new Set(branches.map(b => b[0]));
  const subtrees = {};
  for (let root of roots) {
	subtrees[root] = labelTree(branches.
		filter(b => b.length > 1 && b[0] === root).
		map(b => b.slice(1)));
  }
  return subtrees;
}

function walkLabelTree(lblTree, callback, level=0, baseLabel='') {
  const nodes = Object.keys(lblTree).sort();
  for (let node of nodes) {
	const absoluteLabel = baseLabel + node;
	callback([level, node, absoluteLabel])
	walkLabelTree(lblTree[node], callback, level+1, absoluteLabel + '/');
  }
}

//
// ruTorrent Nested Categorical Label-Sorter "/"
// Version 0.8
// by thezwallrus
//

plugin.loadLabels = theWebUI.loadLabels;
/**
 *
 * @param {Object.<string, number>} c - <label_name, count>
 * @param {Object.<string, number>} s - labels size
 */
theWebUI.loadLabels = function(c, s)
{
	if (plugin.enabled)
	{
		const p = $("#lbll");
		const touchedIds = new Array();
		const labels = Object.keys(c).sort();
	const branches = labels.map(l => l.split('/'));
	const lblTree = labelTree(branches);
	walkLabelTree(lblTree, ([level, node, lbl]) => {
		const shortLabel = '../'.repeat(level) + escapeHTML(node);
		if (lbl in c) {
			const lblSize = this.settings["webui.show_labelsize"] ? " ; " + theConverter.bytes(s[lbl], 2) : "";
			this.labels["-_-_-" + lbl + "-_-_-"] = c[lbl] + lblSize;
			this.cLabels[lbl] = 1;
			touchedIds["-_-_-" + lbl + "-_-_-"] = true;
			if(!$$("-_-_-" + lbl + "-_-_-"))
			{
			p.append( $("<LI>").
				attr("id","-_-_-" + lbl + "-_-_-").
				html(shortLabel + "&nbsp;(<span id=\"-_-_-" + lbl + "-_-_-c\">" + c[lbl] + lblSize + "</span>)").
				mouseclick(theWebUI.labelContextMenu).addClass("cat") );
			}
		} else {
			p.append( $('<LI>').html(shortLabel).css("cursor","default") );
		}
	});
		var actDeleted = false;
		p.children().each(function(ndx,val)
		{
			var id = val.id;
			if(id && !id in touchedIds)
			{
				$(val).remove();
				delete theWebUI.labels[id];
				delete theWebUI.cLabels[id.substr(5, id.length - 10)];
				if(theWebUI.actLbls["plabel_cont"] == id)
					actDeleted = true;
			}
		});
		if(actDeleted)
		{
			this.switchLabel($("#plabel_cont .-_-_-all-_-_-").get(0))
		}
	}
	else
	{
		plugin.loadLabels.call(theWebUI,d);
	}
}
