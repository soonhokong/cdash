$(document).ready(function() {
  /** Load the help page */
  var helpfile = 'help.html';
  if(this.getElementById('cdashuselocal'))
    {
    helpfile = 'local/help.html';
    }
  $('#help').jqm({ajax: helpfile, trigger: 'a.helptrigger'});
  if(this.getElementById('projectname'))
    {
    var projectname = this.getElementById('projectname').value;
    $('#groupsdescription').jqm({ajax: 'groupsDescription.php?project='+projectname, trigger: 'a.grouptrigger'});

    // If we are in advanced view, we setup the advanced view
    if($.cookie('cdash_'+projectname+'_advancedview') == '1')
      {
      $('.advancedview').html("Simple View");
      $('.advancedviewitem').show();

      $timeheaders = $(".timeheader");
      $timeheaders.each(
        function(index) {
         var colspan = $(this).attr('colspan');
         colspan++;
         $(this).attr('colspan',colspan);
        });
      }
    }

  // Check if we have a cookie for refreshing the page
   var refresh_cookie = $.cookie('cdash_refresh');
   if(refresh_cookie)
      {
      $('.autorefresh').css('font-weight','bold');
      setTimeout( function(){if($.cookie('cdash_refresh')) location.reload()},refresh_cookie); // do the refresh
      }

  /** Enable the autorefresh */
  $('.autorefresh').click(function() {
     var refresh_cookie = $.cookie('cdash_refresh');

    if(refresh_cookie)
      {
      $.cookie('cdash_refresh', null);
      $(this).css('font-weight','normal');
      }
    else
      {
      $.cookie('cdash_refresh',60000); // 60 seconds refresh
      location.reload();
      }
  });

  /** qtip on the build time elapsed */
  $('.builddateelapsed').qtip({
     content: {attr: 'alt'},
     style: {classes: 'ui-tooltip-blue'},
     position: {
      my: 'top right',  // Position my top left...
      at: 'bottom left' // at the bottom right of...
      }
   })

  /** qtip on the build time elapsed */
  $('.buildinfo').qtip({
     content: {attr: 'alt'},
     style: {classes: 'ui-tooltip-blue'},
     position: {
      my: 'top left',  // Position my top left...
      at: 'bottom right' // at the bottom right of...
      }
   })


  /** Show/Hide time */
  $('.advancedview').click(function()
    {
    var projectname = document.getElementById('projectname').value;

    if ($('.advancedview').html() == "Simple View")
      {
      $('.advancedview').html("Advanced View");
      $('.advancedviewitem').hide();
      $timeheaders = $(".timeheader");
      $timeheaders.each(
        function(index) {
         var colspan = $(this).attr('colspan');
         colspan--;
         $(this).attr('colspan',colspan);
        });
      $.cookie('cdash_'+projectname+'_advancedview','0');
      }
    else
      {
      $('.advancedview').html("Simple View");
      $('.advancedviewitem').show();

      $timeheaders = $(".timeheader");
      $timeheaders.each(
        function(index) {
         var colspan = $(this).attr('colspan');
         colspan++;
         $(this).attr('colspan',colspan);
        });
      $.cookie('cdash_'+projectname+'_advancedview','1');
      }
    });

  /** Build name */
  $.tablesorter.addParser({
      // set a unique id
      id: 'buildname',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            var t = s;
            var i = s.indexOf("<a ");
            if(i>0)
              {
              var j = s.indexOf(">",i);
              var k = s.indexOf("</a>",j);
              t = s.substr(j+1,k-j-1);
              }
            return t.toLowerCase();
        },
        // set type, either numeric or text
        type: 'text'
    });

  /** Update */
  $.tablesorter.addParser({
      // set a unique id
      id: 'numericvalue',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
          // format your data for normalization
            // Remove the <sub></sub>
            var i = s.indexOf("<sub>");
            if(i!=-1)
              {
              j = s.indexOf("</sub>");
              s = s.substr(0,i)+s.substr(j+6);
              }

            // Remove the <div></div>
            i = s.indexOf("<div");
            if(i!=-1)
              {
              j = s.indexOf(">",i);
              s = s.substr(0,i)+s.substr(j+1);
              }
            i = s.indexOf("</div>");
            if(i!=-1)
              {
              s = s.substr(0,i)+s.substr(i+6);
              }

            var i = s.indexOf("<a href");
            if(i==-1) // IE
              {
              i = s.indexOf("<A href");
              }

            // We don't have a <a href
            if(i==-1)
              {
              return s;
              }

            var j = s.indexOf(">",i);
            var k = s.indexOf("</a>",j);
            if(k==-1) // IE
              {
              k = s.indexOf("</A>");
              }
            var t = s.substr(j+1,k-j-1);
            return t.toLowerCase();
        },
        // set type, either numeric or text
        type: 'numeric'
    });

  /** percent for coverage */
  $.tablesorter.addParser({
      // set a unique id
      id: 'percentage',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            var i = s.indexOf("<a");
            if(i == -1)
              {
              var t = s.substr(0,s.length-1);
              return t.toLowerCase();
              }
            var j = s.indexOf(">",i);
            var k = s.indexOf("</a>",j);
            var t = s.substr(j+1,k-j-2);
            return t.toLowerCase();
        },
        // set type, either numeric or text
        type: 'numeric'
    });

  /** elapsed time */
  $.tablesorter.addParser({
      // set a unique id
      id: 'elapsedtime',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            var i = s.indexOf("<span");
            if(i == -1) return s;

            var j = s.indexOf(">",i);
            var k = s.indexOf("</span>",j);
            var t = s.substr(j+1,k-j-1);
            return t.toLowerCase();
        },
        // set type, either numeric or text
        type: 'numeric'
    });

  /** Coverage percent table */
  $.tablesorter.addParser({
      // set a unique id
      id: 'coveragepercent',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            var i = s.indexOf("percentvalue");
            if(i!=-1)
              {
              var j = s.indexOf(">",i);
              var k = s.indexOf("</div>",j);
              var t = s.substr(j+1,k-j-2);
              return t.toLowerCase();
              }
           return false;
        },
        // set type, either numeric or text
        type: 'numeric'
    });

  /** numeric for dynamic analysis */
  $.tablesorter.addParser({
      // set a unique id
      id: 'dynanalysismetric',
      is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            var i = s.indexOf("<a");
            var j = s.indexOf(">",i);
            var k = s.indexOf("</a>",j);
            var t = s.substr(j+1,k-j-1);
            return t.toLowerCase();
        },
        // set type, either numeric or text
        type: 'numeric'
    });

  // Initialize the viewTest tables
  $tabs = $("#viewTestTable");
  var nrows = 0;
  if(document.getElementById('viewTestTable'))
    {
    var nrows = document.getElementById('viewTestTable').getElementsByTagName('thead')[0].getElementsByTagName('th').length;
    }

 if(nrows==3)
    {
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'buildname'},
                1: { sorter:'buildname'},
                2: { sorter:'elapsedtime'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }
 else if(nrows==4 && document.getElementById('showtesttimediv'))
    {
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'buildname'},
                1: { sorter:'buildname'},
                2: { sorter:'buildname'},
                3: { sorter:'elapsedtime'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }
  else if(nrows==4)
    {
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'buildname'},
                1: { sorter:'buildname'},
                2: { sorter:'elapsedtime'},
                3: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }
  else if(nrows==5)
    {
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'buildname'},
                1: { sorter:'buildname'},
                2: { sorter:'buildname'},
                3: { sorter:'elapsedtime'},
                4: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }
  else if(nrows==6)
    {
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'buildname'},
                1: { sorter:'buildname'},
                2: { sorter:'buildname'},
                3: { sorter:'elapsedtime'},
                4: { sorter:'text'},
                5: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }
  // Initialize the testSummary tables
  if($tabs.length==0)
    {
    $tabs = $("#testSummaryTable");
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'text'},
                1: { sorter:'buildname'},
                2: { sorter:'text'},
                3: { sorter:'buildname'},
                4: { sorter:'digit'},
                5: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }

  // Initialize the queryTests tables
  if($tabs.length==0)
    {
    $tabs = $("#queryTestsTable");
    if (document.getElementById('showtesttimediv'))
    {
    $tabs.each(function(index) {
        $(this).tablesorter({
               headers: {
                   0: { sorter:'text'},
                   1: { sorter:'buildname'},
                   2: { sorter:'text'},
                   3: { sorter:'buildname'},
                   4: { sorter:'buildname'},
                   5: { sorter:'digit'},
                   6: { sorter:'text'},
                   7: { sorter:'text'}
               },
             debug: false,
             widgets: ['zebra']
           });
         });
    }
    else
    {
    $tabs.each(function(index) {
        $(this).tablesorter({
               headers: {
                   0: { sorter:'text'},
                   1: { sorter:'buildname'},
                   2: { sorter:'text'},
                   3: { sorter:'buildname'},
                   4: { sorter:'digit'},
                   5: { sorter:'text'},
                   6: { sorter:'text'}
               },
             debug: false,
             widgets: ['zebra']
           });
         });
    }
    }

  //Initialize the coverage table
  if($tabs.length==0)
    {
    $tabs = $("#coverageTable");
    if($("#coverageType").val() == "gcov")
    {
    $tabs.each(function(index) {
        $(this).tablesorter({
               headers: {
                   0: { sorter:'buildname'},
                   1: { sorter:'text'},
                   2: { sorter:'coveragepercent'},
                   3: { sorter:'digit'},
                   4: { sorter:'text'},
                   5: { sorter:'text'},
                   6: { sorter:'text'}
               },
             debug: false,
             widgets: ['zebra']
           });
         });
    }
    else //bull's eye
      {
      $tabs.each(function(index) {
        $(this).tablesorter({
             headers: {
             0: { sorter:'buildname'},
               1: { sorter:'text'},
               2: { sorter:'coveragepercent'},
               3: { sorter:'digit'},
               4: { sorter:'digit'},
               5: { sorter:'text'},
               6: { sorter:'text'},
               7: { sorter:'text'}
              },
           debug: false,
           widgets: ['zebra']
           });
         });
       }
    } // end coverage

  // Initialize the userStatistics table
  if($tabs.length==0)
    {
    $tabs = $("#userStatistics");
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'text'},
                1: { sorter:'digit'},
                2: { sorter:'digit'},
                3: { sorter:'digit'},
                4: { sorter:'digit'},
                5: { sorter:'digit'},
                6: { sorter:'digit'},
                7: { sorter:'digit'},
                8: { sorter:'digit'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }

  // Initialize the subproject table
  if($tabs.length==0)
    {
    $tabs = $("#subproject");
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'text'},
                1: { sorter:'digit'},
                2: { sorter:'digit'},
                3: { sorter:'digit'},
                4: { sorter:'digit'},
                5: { sorter:'digit'},
                6: { sorter:'digit'},
                7: { sorter:'digit'},
                8: { sorter:'digit'},
                9: { sorter:'digit'},
                10: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    }

  // Initialize the indextable table
  if($tabs.length==0)
    {
    $tabs = $("#indexTable");
    $tabs.each(function(index) {
    $(this).tablesorter({
        headers: {
           0: { sorter:'buildname'},
           1: { sorter:'text'},
           2: { sorter:'elapsedtime'}
          },
          debug: false,
          widgets: ['zebra']
        });
      });
    }

  // If all the above are not working then it should be the index table
  if($tabs.length==0)
    {
    // Initialize the Index tables
    $tabs = $(".tabb",this);
    $tabs.each(
     function(index) {
      var tableid = this.id;
      if(tableid == "coveragetable" || tableid == "dynamicanalysistable")
        {
        return;
        }
       $(this).tablesorter({
              headers: {
                  0: { sorter:'buildname'},
                  1: { sorter:'buildname'},
                  2: { sorter:'numericvalue'},
                  3: { sorter:'elapsedtime'},
                  4: { sorter:'numericvalue'},
                  5: { sorter:'numericvalue'},
                  6: { sorter:'elapsedtime'},
                  7: { sorter:'numericvalue'},
                  8: { sorter:'numericvalue'},
                  9: { sorter:'elapsedtime'},
                  10: { sorter:'numericvalue'},
                  11: { sorter:'numericvalue'},
                  12: { sorter:'numericvalue'},
                  13: { sorter:'elapsedtime'},
                  14: { sorter:'elapsedtime'},
                  15: { sorter:'text'}
              },
            debug: false,
            widgets: ['zebra']
          });

      // Get the cookie
      var cookiename = "cdash_table_sort_"+tableid;
      var cook = $.cookie(cookiename); // get cookie
      if(cook)
        {
        var cookArray = cook.split(',');
        var sortArray = new Array();
        var j=0;
        for(var i=0; i < cookArray.length; i+=2)
          {
          sortArray[j] = [cookArray[i],cookArray[i+1]];
          j++;
          }
        $(this).trigger("sorton",[sortArray]);
        }
      });

    // Initialize the coverage table
    $tabs = $("#coveragetable");
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'text'},
                1: { sorter:'text'},
                2: { sorter:'percentage'},
                3: { sorter:'numericvalue'},
                4: { sorter:'numericvalue'},
                5: { sorter:'elapsedtime'},
                6: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });

    // Initialize the dynamic analysis table
    $tabs = $("#dynamicanalysistable");
    $tabs.each(function(index) {
     $(this).tablesorter({
            headers: {
                0: { sorter:'text'},
                1: { sorter:'text'},
                2: { sorter:'text'},
                3: { sorter:'numericvalue'}, // not percent but same format
                4: { sorter:'elapsedtime'},
                5: { sorter:'text'}
            },
          debug: false,
          widgets: ['zebra']
        });
      });
    } // end indextable

});
