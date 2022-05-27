  /* Print Receipt to PDF */

  // payment transactions page
  var RecordsTable_length;

$(document).ready(function () {

        //////////////////////////////
        // pdf export click function
        //////////////////////////////

        $('body').on('click', '#print-pdf-receipt', function () {

            //////////////////////////////
            // initial receipt_document set-up
            //////////////////////////////

            var receipt_doc = new jsPDF('p');
            var pdf_margin = 14;

            //////////////////////////////
            // set up functions
            //////////////////////////////

            function printReceipt() {

                var receipt = $("#pdfReceiptModal .modal-body");

                receipt.each(function () {

                    html2canvas($(this), {

                        profile: true,
                        allowTaint: true,
                        letterRendering: true,
                        useCORS: true,

                        onrendered: function (canvas) {

                            // get graph and convert it to an img
                            var imageData = canvas.toDataURL("image/png",
                                wid = canvas.width, 
                                hgt = (canvas.height)-((canvas.height)*(10/100)) );
                            var hratio = hgt / wid
                            var width = receipt_doc.internal.pageSize.width;
                            var height = width * hratio;                           

                            receipt_doc.addImage(
                                imageData, // img
                                'JPEG', // img format
                                pdf_margin, // left margin
                                pdf_margin, // top margin
                                width - (pdf_margin * 2), // img width
                                height // img height
                            );	                   

                            jspdfSave();

                        } // close onrendered

                    }); // close html2canvas

                }); // close graphs.each

            }

            function jspdfSave() {

                // get current date for filename
                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var current_date = d.getFullYear() + '_' + (month < 10 ? '0' : '') + month + '_' + (day <
                        10 ? '0' : '') +
                    day;

                // create filename
                var filename = "Receipt " + current_date;

                // save page
                receipt_doc.save(filename + ".pdf");

            }

            //////////////////////////////
            // create pdf based on page content
            //////////////////////////////

            printReceipt();

        }); // close export onclick

    }); // close receipt_doc.ready


  /* PDF Export */

  // use class="jspdf-table" for tables
  // use class="jspdf-graph" for graphs/visuals

  //////////////////////////////
  // get client data for pdf header
  //////////////////////////////

 
  
  var logo_url="/parcx/dist/img/pdf-logo/logo_small_white_bg.png";
  var address_text="";
  var address_width="";

 

  function modalHide() {
    setTimeout(function () {
      if ($('#pdf-loader-modal').hasClass('show')) {
        $("#pdf-loader-modal").modal('hide');
      }
    }, 500);
  }

  $(document).ready(function () {

    //////////////////////////////
    // functions to load external images
    //////////////////////////////

    function imgToBase64(url, callback) {
      if (!window.FileReader) {
        callback(null);
        return;
      }
      var xhr = new XMLHttpRequest();
      xhr.responseType = 'blob';
      xhr.onload = function () {
        var reader = new FileReader();
        reader.onloadend = function () {
          callback(reader.result.replace('text/xml', 'image/jpeg'));
        };
        reader.readAsDataURL(xhr.response);
      };
      xhr.open('GET', url);
      xhr.send();
    }

    function getImage() {
      imgToBase64(logo_url, function (base64) {
        base64Img = base64;
      });
    }

    //////////////////////////////
    // load logo image for header
    //////////////////////////////

    var base64Img;

    getImage();

    //////////////////////////////
    // pdf export click function
    //////////////////////////////

    $('#export_pdf_report').click(function (event) {

      // show modal
      $('#pdf-loader-modal').modal('show');

      //////////////////////////////
      // initial document set-up
      //////////////////////////////

      var doc = new jsPDF('l');

      var pdf_margin = 14;
      var pageHeaderHeight = 30;
      var reportHeaderHeight = 38; // pt

      //////////////////////////////
      // set up variables for header
      //////////////////////////////

      // page title
      if ($("#pdf-report-header").length != 0) {
        var page_title = $('#pdf-report-header').html();
      } else {
        var page_title = "null";
      }

      // add 'report' to page title
//      if (page_title.indexOf("Report") <= 0) {
//        page_title += " Report";
//      }

      // export date and time
      var export_date = new Date($.now());
      export_date = "Export Date: " + export_date.toString()

      // report duration
      if ($("#reservationtime").length == 0) {
        var report_duration = "Live Report"
      } else {
        // from to vars used from init-daterangpicker.js
        var report_duration = "Report Duration: " + from + " to " + to
      }

      //////////////////////////////
      // add report header to pdf
      //////////////////////////////

      // add page title, export date and report duration to pdf
      
      doc.setFont("Amiri");
      doc.setFontSize(13);
      doc.text(pdf_margin, (10 + pageHeaderHeight), page_title);
      doc.setFontSize(11);
      doc.text(pdf_margin, (18 + pageHeaderHeight), report_duration);
      doc.text(pdf_margin, (24 + pageHeaderHeight), export_date);

      // draw line
      doc.line(14, pageHeaderHeight, (doc.internal.pageSize.width - 14), pageHeaderHeight);

      //////////////////////////////
      // set up functions
      //////////////////////////////

      var table_heading

      function jspdfTable() {

        if($(".jspdf-table").attr("id")=="RecordsTable")
          {
          RecordsTable_length=$('select[name="RecordsTable_length"]').val();          
          $('select[name="RecordsTable_length"]')
          .val('-1')
          .trigger('change');   
          }


        var elem = $(".jspdf-table");
        var tableY = (elementHeight - 6)

        elem.each(function () {

          // manage margins for multiple tables
          if ($(".jspdf-table").length > 1) {

            // if this is the first table, add top margin for report header
            if (this === elem[0]) {
              tableY = (elementHeight - 6)
            }
            // else, switch margin to end pos of previous table
            else {
              tableY = doc.autoTableEndPosY() + 10;
            }

            // add extra margin if table has a heading
            table_heading = $(this).prev('.header').text();

            if (table_heading !== undefined) {
              tableY += 5;
              doc.text(table_heading, pdf_margin, (tableY - 5));
            }

          }

          // get table data
          
          var res = doc.autoTableHtmlToJson(this);
                   
          doc.internal.scaleFactor = 2.834645669291339; // default

          // create table


          doc.autoTable(res.columns, res.data, {              
            styles: {                
              cellPadding: [3, 2, 3, 1.4], // top, right, bottom, left
              overflow: 'linebreak',
              columnWidth: 'auto', // 'wrap' could lead to some columns getting cut off
              fontSize: 10,
              valign: 'middle',
              font: 'Amiri',
              fontStyle: 'normal',
            },
            startY: tableY,
            margin: {
              bottom: 15,
              top: 30
            },
          });


        });

        // save file
        jspdfSave();

      }

      function jspdfGraph() {

        var graphs = $(".jspdf-graph");
        var remaining = graphs.length;
        var current = 0;
        var graphHeight = [];
        var offsetValue = 0;
        var pageCount = 1;
        var previousGraphIndex;

        graphs.each(function () {

          html2canvas($(this), {

            profile: true,
            allowTaint: true,
            letterRendering: true,
            useCORS: true,

            onrendered: function (canvas) {

              // get graph and convert it to an img
              var imageData = canvas.toDataURL("image/png",
                wid =
                canvas.width, hgt =
                canvas.height);
              var hratio = hgt / wid
              var width = doc.internal.pageSize.width;
              var height = width * hratio;
              graphHeight[current] = height;

              pageHeight = doc.internal.pageSize.height;
              pageHeight -= (pdf_margin * 2)
              pageHeight = Math.round(pageHeight)

              // for the 1st graph, add top margin for report header
              if (remaining === graphs.length) {
                var img_top_margin = pageHeaderHeight +
                  reportHeaderHeight - 6 + offsetValue;
              }
              // for all graphs apart from the 1st
              // if graph exceeds remaining space on pg, create new pg
              else {

                previousGraphIndex = current - 1
                offsetValue = graphHeight[
                    previousGraphIndex] +
                  pageHeaderHeight + 6 +
                  reportHeaderHeight +
                  graphHeight[current];
                offsetValue = Math.round(offsetValue);

                if (offsetValue > pageHeight) {

                  doc.addPage();

                  // draw line
                  doc.line(14, pageHeaderHeight, (doc.internal.pageSize
                    .width - 14), pageHeaderHeight);

                  offsetValue = 0; // restart height position
                  offsetValue += pageHeaderHeight
                  offsetValue += 8 // buffer
                 

                  pageCount += 1;

                } else {
                  

                  // reset offsetValue for positioning
                  // different offsetValue for first page versus other pages due to report header

                  if (pageCount === 1) {
                    offsetValue = pageHeaderHeight +
                      reportHeaderHeight +
                      graphHeight[
                        previousGraphIndex] - 2;
                  } else {
                    offsetValue = pageHeaderHeight +
                      graphHeight[previousGraphIndex] +
                      12;
                  }

                }

                // add top margin for page header
                var img_top_margin = offsetValue;

              }

              doc.addImage(
                imageData, // img
                'JPEG', // img format
                pdf_margin, // left margin
                img_top_margin, // top margin
                width - (pdf_margin * 2), // img width
                height // img height
              );

              // increase graph counter
              current += 1
              remaining--;

              // if no graphs remain
              if (remaining === 0) {

                //////////////////////////////
                // add table data & save
                //////////////////////////////

                // if page only has graph content
                if ($(".jspdf-table").length == 0) {

                  jspdfSave();

                }
                // if page has table and graph content
                else {

                  elementHeight = height; // height of image
                  elementHeight += 8 // buffer for graphic bottom margin
                  elementHeight += reportHeaderHeight // add height of header
                  elementHeight += pageHeaderHeight

                  if (previousGraphIndex !== undefined) {
                    elementHeight += graphHeight[previousGraphIndex]
                  }

                  jspdfTable();

                }

              }

            } // close onrendered

          }); // close html2canvas

        }); // close graphs.each

      }

      function addHeader() {

        // set margins
        var headerBuffer = 0.5
        pdf_margin -= headerBuffer;

        // add logo image
        if (base64Img) {
          doc.addImage(base64Img, 'JPEG', (pdf_margin + 1), pdf_margin, 0, 0);
        }

        // add address text
        var address_position = doc.internal.pageSize.width - address_width - pdf_margin - 2;

        doc.text(address_position, 16, address_text, {
          maxWidth: address_width,
          align: "left"
        });

        // reset margin
        pdf_margin += headerBuffer;

      }

      var pageCount
      var currentPage

      function addFooter() {

        // add page count text
        var footerString = "Page " + currentPage + " of " + pageCount;
        doc.text(footerString, pdf_margin, doc.internal.pageSize.height - 10);

      }

      function jspdfSave() {

        // add header & footer to all pages
        pageCount = doc.internal.getNumberOfPages();

        for (currentPage = 1; currentPage <= pageCount; currentPage++) {
          doc.setPage(currentPage);
          addHeader();
          addFooter();
        }
        var report_date;
        if(!daterange)
            {
            // get current date for filename
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            report_date = d.getFullYear() + '_' + (month < 10 ? '0' : '') + month + '_' + (day < 10 ? '0' : '') +
              day;
            }
        else
            report_date=daterange;
        
        

        // create filename
        var filename = "PARCX "+page_title + " " + report_date;          

        // save page
        doc.save(filename + ".pdf");

        modalHide();

        if($(".jspdf-table").attr("id")=="RecordsTable")
        {        
        $('select[name="RecordsTable_length"]')
        .val(RecordsTable_length)
        .trigger('change');   
        }

      }

      //////////////////////////////
      // create pdf based on page content
      //////////////////////////////

      var elementHeight

      // case 1 - if page only has table content
      if ($(".jspdf-graph").length == 0) {
        elementHeight = reportHeaderHeight + pageHeaderHeight
        jspdfTable();
      }
      // case 2 - page has table & graph // or page only has graphs
      else {
        jspdfGraph();
      }

    }); // close export onclick

  }); // close doc.ready

  /* End / PDF Export */
