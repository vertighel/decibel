//////////////// TABLE ////////////////
cinetable = function(config) {
    var columns = [];
    var what = "";
    var lines = "";
    var id = "";

    var tbl = function(selection) {
	/// default parameters
	if (columns.length == 0) columns = d3.keys(selection.data()[0][0]);
	if (lines.what == 0) lines = ""; 
	if (lines.length == 0) lines = "";
	if (id.length == 0) id = "";


	/// creating the table
	selection.selectAll('table').data([0]).enter().append('table');
	var table = selection.select('table');
	table.selectAll('thead').data([0]).enter().append('thead');
	var thead = table.select('thead');
	table.selectAll('tbody').data([0]).enter().append('tbody');
	var tbody = table.select('tbody');

	/// appending the header row
	var th = thead.selectAll("th")
     	    .data(columns);

	th.enter().append("th");
        th.text(function(d) { return d });
	th.exit().remove();
		
	// creating a row for each object in the data, with filtering
	var rows = tbody.selectAll('tr').data(tbody.data()[0]);
	// var rows = tbody.selectAll('tr').data(tbody.data()[0]).filter(function(d) { 
	//     return d[what]==lines
	// });
	
	rows.enter().append("tr")
	    .attr('data-'+id, function(d){return d[id]})

	rows.attr('data-row',function(d,i){return i})
    	    .on('mouseover', function(d){mouseover(d[id],id)})
    	    .on('mouseout', function(d){mouseover(null,id)})
	     .on('click',function(d){mouseclick(d,id)})
//	    .on('click',mouseclick)

	rows.exit().remove();

	/// creating a cell for each column in the rows
	var cells = rows.selectAll("td")
            .data(function(row) {
		return columns.map(function(key) {
                    return {key:key, value:row[key]};
		});
            });

	cells.enter().append("td");	
	cells.text(function(d) { return d.value; })
	    .attr('data-col',function(d,i){return i})
	    .attr('data-key',function(d,i){return d.key});	
	cells.exit().remove();

	return tbl;
    };


    ////////////////// Methods for table //////////////////
    tbl.columns = function(_) {
	if (!arguments.length) return columns;
	columns = _;
	return this;
    };
    
    tbl.lines = function(_,__) {
	if (!arguments.length) return rows;
	what = _;
	lines = __;
	return this;
    };
    
    tbl.id = function(_) {
	if (!arguments.length) return id;
	id = _;
	return this;
    };
    
    return tbl;
};
