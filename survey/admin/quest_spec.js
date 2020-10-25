var selFocused = 0;
var AFOCUSED=1;
var SFOCUSED=2;

function afocused()
{
	selFocused = AFOCUSED;
}

function sfocused()
{
	selFocused = SFOCUSED;
}

function questSortOptions (selElt) 
{
	for (i = 0; i <selElt.options.length; i++) 
	{
    	for (j = 0; j<selElt.options.length-1; j++) 
		{
        	// if an option is greater than the next option, swap them
         	if (selElt.options[j].text > selElt.options[j+1].text) 
			{
            	tmpTxt = selElt.options[j].text;
            	tmpVal = selElt.options[j].value;
            	selElt.options[j].text = selElt.options[j+1].text ;
            	selElt.options[j].value = selElt.options[j+1].value;
            	selElt.options[j+1].text = tmpTxt ;
            	selElt.options[j+1].value = tmpVal;
         	}
      	}
   	}
}

function questMoveLeft()
{
    var alist = document.qform.aquestions;
	if (alist.selectedIndex >= 0)
	{
		var mvitem = alist.options[alist.selectedIndex];
		if (mvitem != null)
		{
			slist = document.qform.squestions;
			alist.options[alist.selectedIndex] = null;
			alist.selectedIndex = 0;
            slist.options[slist.options.length] = mvitem;
		}
	}
}

function questMoveRight() 
{
    var slist = document.qform.squestions;
	if (slist.selectedIndex >= 0)
	{
		var mvitem = slist.options[slist.selectedIndex];
		if (mvitem != null)
		{
			alist = document.qform.aquestions;
			slist.options[slist.selectedIndex] = null;
            alist[alist.options.length] = mvitem;
			questSortOptions(alist);
			alist.selectedIndex = 0;
		}
	}
}

function questMoveUp()
{
	var slist = document.qform.squestions;
	if(slist.selectedIndex > 0)
	{
		tmpTxt = slist.options[slist.selectedIndex].text;
		tmpVal = slist.options[slist.selectedIndex].value;
		slist.options[slist.selectedIndex].text =
			slist.options[slist.selectedIndex-1].text;
		slist.options[slist.selectedIndex].value =
			slist.options[slist.selectedIndex-1].value;
		slist.options[slist.selectedIndex-1].text = tmpTxt;
		slist.options[slist.selectedIndex-1].value = tmpVal;
		slist.selectedIndex--;
	}
}

function questMoveDown()
{
	var slist = document.qform.squestions;
    if(slist.selectedIndex < (slist.options.length-1))
	{
		tmpTxt = slist.options[slist.selectedIndex].text;
		tmpVal = slist.options[slist.selectedIndex].value;
		slist.options[slist.selectedIndex].text =
			slist.options[slist.selectedIndex+1].text;
		slist.options[slist.selectedIndex].value =
			slist.options[slist.selectedIndex+1].value;
		slist.options[slist.selectedIndex+1].text = tmpTxt;
		slist.options[slist.selectedIndex+1].value = tmpVal;
		slist.selectedIndex++;
	}
}

function questOnSubmit()
{
	var theForm = document.qform;
	var slist = theForm.squestions;
	var qs = "";
	for (i = 0; i < slist.options.length; i++)
	{
		if(i > 0)
		{
			qs += ",";
		}
		var ndx = slist.options[i].value.indexOf(",");
		var qnum = slist.options[i].value;
		if(ndx > 0)
		{
			qnum = slist.options[i].value.substring(0, ndx);
		}

		qs += qnum;
	}

	document.qform.survey_questions.value = qs;
	return true;
}

function questDetail()
{
	var alist = document.qform.aquestions;
	if(selFocused == SFOCUSED)
	{
		alist = document.qform.squestions;
	}

	if (alist.selectedIndex < 0)
	{
		return;
	}
	var vals = alist.options[alist.selectedIndex].value.split(",");
	if(vals.length != 2)
	{
		alert("Logic error. Invalid question encountered");
		return;
	}

	var quest = alist.options[alist.selectedIndex].text;
	var qnum = vals[0];
	var qtype = questionTypes[vals[1]];

	var winl = (screen.width - 400) / 2;
	var wint = (screen.height - 200) / 2;
	var nw = window.open('','', 'height=200,width=400,top='+wint+',left='
		+winl+',scrollbars,resizable');
	var nd = nw.document;
	var nc = '<html><head><title>Question Details</title>';
	nc += '<style> .bg1 { background-color: #E3E4E0; }';
	nc += '.bg2 { background-color: #CCCCCC; }';
	nc += '.bg3 { background-color: #DDE1DE; }</style></head><body>';
	nc += '<h3>Question Details</h3>';
	nc += '<table border="0" width="100%"><tr class="bg2">';
	nc += '<td>Number</td><td>Question</td><td>Type</td></tr><tr>';
	nc += '<td>'+qnum+'</td><td>'+quest+'</td><td>'+qtype+'</td></tr>';

	nc += '<tr><td></td></tr><tr><td colspan="3" align="center">';
	nc += '<form><input type="button" value="Close" onClick="window.close()">';
	nc += '</form></td></tr>';

	nc += '</table>';
	nc += '</body></html>';
	nd.write(nc);
	nd.close();
}
