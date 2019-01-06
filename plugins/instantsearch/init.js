/** Time before searching **/
theSearchEngines.instantSearchDelay = 220;

theSearchEngines.filterBySearch = function(sId)
{
	var table = theWebUI.getTable("trt");
	if(table.getValueById(sId, "name").toLowerCase().indexOf($("#query").val().toLowerCase()) >- 1)
		table.unhideRow(sId);
	else 
		table.hideRow(sId);
}

theSearchEngines.instantSearch = function()
{
	if(theSearchEngines.current>=0)
		return;
	if(theSearchEngines.instantSearchTimer)
		clearTimeout(theSearchEngines.instantSearchTimer);
	
	theSearchEngines.instantSearchTimer = setTimeout("theSearchEngines.doInstantSearch()", theSearchEngines.instantSearchDelay);
}

theSearchEngines.doInstantSearch = function()
{
	theWebUI.switchLabel($("#-_-_-all-_-_-")[0]);
	var table = theWebUI.getTable("trt");
	table.scrollTo(0);
	for(var k in theWebUI.torrents)
		theSearchEngines.filterBySearch(k);
	table.clearSelection();
	if(theWebUI.dID != "")
    {
		theWebUI.dID = "";
		theWebUI.clearDetails();
    }
   	table.refreshRows();
}

plugin.onRemove = function()
{
	$("#query").keydown(null);
}

$("#query").keydown(theSearchEngines.instantSearch);
	