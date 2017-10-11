jQuery(document).ready(function($) {
  /*Preloader*/
  var preloader = '<div class="aecid-preloader">';
  preloader += '  <img src="/sites/default/files/aecid-preloader-intranet.svg" width="150" height="150">';
  preloader += '  <label>';
  //preloader += '    <h3>100%</h3>';
  preloader += '    <small>Completando...</small>';
  preloader += '  </label>';
  preloader += '</div>';

  $('.aecid-plandirector-subgroup-container .aecid-plandirector-subgroup .aecid-plandirector-subgroup-title').click(function(event) {
    $(this).parent().find('.aecid-plandirector-subgroup-data').toggle();
  });

  $('.aecid-plandirector-export-excel').click(exportPlanDirectorToExcel);

  $('.aecid-plandirector-export-pdf').click(exportPlanDirectorToPdf);

  $('.aecid-plandirector-subgroup-container .aecid-plandirector-subgroup .aecid-plandirector-subgroup-data-name').click(function(event) {
    $.ajax({
      url: $(this).attr('href'),
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $('div[data-at-row="main"]').css({'opacity':'0.2'});
        $('body').append(preloader);
        $('.aecid-preloader').show();
      },
      success: function(data) {
        $('.aecid-preloader').hide();
        $('.aecid-preloader').remove();
        $('div[data-at-row="main"]').css({'opacity':'1'});
        $('body').append(data['data']);
        $('.aecid-intranet-lightbox').show();

        /*Cierra lightbox*/
        $('.aecid-intranet-lightbox-close').click(function(event) {
          $('.aecid-intranet-lightbox').hide();
          $('.aecid-intranet-lightbox').remove();
        });
      }
    });

    return false;
  });

  function exportPlanDirectorToExcel() {
    $.ajax({
      url: $(this).attr('href'),
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $('div[data-at-row="main"]').css({'opacity':'0.2'});
        $('body').append(preloader);
        $('.aecid-preloader').show();
      },
      success: function(data) {
        $('.aecid-preloader').hide();
        $('.aecid-preloader').remove();
        $('div[data-at-row="main"]').css({'opacity':'1'});
        $('body #table_inter_export').html(data);
        var date = new Date();
        var year = date.getUTCFullYear();
        var month = date.getUTCMonth();
        var day = date.getUTCDate();
        var hour = date.getUTCHours();
        var minutes = date.getUTCMinutes();
        var seconds = date.getUTCSeconds();
        var utc = Date.UTC(year, month, day, hour, minutes, seconds);
        $('body #table_inter_export').tableExport({
          type:'xlsx',
          fileName: 'plan_director.' + utc,
          worksheetName: 'Plan Director'
        });
      }
    });

    return false;
  }

  function exportPlanDirectorToPdf() {
    $.ajax({
      url: $(this).attr('href'),
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $('div[data-at-row="main"]').css({'opacity':'0.2'});
        $('body').append(preloader);
        $('.aecid-preloader').show();
      },
      success: function(data) {
        $('.aecid-preloader').hide();
        $('.aecid-preloader').remove();
        $('div[data-at-row="main"]').css({'opacity':'1'});
        $('body #table_inter_export').html(data);
        var date = new Date();
        var year = date.getUTCFullYear();
        var month = date.getUTCMonth();
        var day = date.getUTCDate();
        var hour = date.getUTCHours();
        var minutes = date.getUTCMinutes();
        var seconds = date.getUTCSeconds();
        var utc = Date.UTC(year, month, day, hour, minutes, seconds);
        $('body #table_inter_export').tableExport({
          type:'pdf',
          jspdf: {
            format: 'a4',//595px x 842px
            margins: {left:10, right:10, top:10, bottom:10},
            orientation: 'l',
            autotable: {
              tableWidth: '100%',
              styles: {
                overflow: 'linebreak',
                fontSize: 12,
                columnWidth: 205.5 //ancho o alto a4 / cantidad de columnas
              },
              headerStyles: {
                fillColor: [216, 23, 57],
                textColor: 255,
                fontStyle: 'bold',
                halign: 'center'
              }
            }
          },
          fileName: 'plan_director.' + utc,
          htmlContent: true,
          worksheetName: 'Plan Director'
        });
      }
    });

    return false;
  }
});
