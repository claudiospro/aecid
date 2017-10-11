jQuery(document).ready(function($) {
  var checked_inter_count = 0;

  /*Preloader*/
  var preloader = '<div class="aecid-preloader">';
  preloader += '  <img src="/sites/default/files/aecid-preloader-intranet.svg" width="150" height="150">';
  preloader += '  <label>';
  preloader += '    <h3>100%</h3>';
  preloader += '    <small>Completando...</small>';
  preloader += '  </label>';
  preloader += '</div>';

  /*Tipo de Cambio - Vista , agrupacion de etiquetas*/
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content').append('<div class="cambiotipo-container" id="cambiotipo-1"></div>');
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content').append('<div class="cambiotipo-container" id="cambiotipo-2"></div>');
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content > h4:nth-child(1)').appendTo('body.path-intranet-tipocambio .view-tipo-cambio .view-content #cambiotipo-1');
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content > table:nth-child(1)').appendTo('body.path-intranet-tipocambio .view-tipo-cambio .view-content #cambiotipo-1');
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content > h4:nth-child(1)').appendTo('body.path-intranet-tipocambio .view-tipo-cambio .view-content #cambiotipo-2');
  $('body.path-intranet-tipocambio .view-tipo-cambio .view-content > table:nth-child(1)').appendTo('body.path-intranet-tipocambio .view-tipo-cambio .view-content #cambiotipo-2');

  /*Exportar Excel Simplificado*/
  $('.inter-export-simple-excel-btn').click(function(event) {
    inter_items_export = '';
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
        var nid = $('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:last-child .inter-item').attr('nid');
        inter_items_export += nid + ',';
      }
    }

    if(checked_inter_count > 0){
      $.ajax({
        url: '/intranet/intervenciones/'+inter_items_export+'/export',
        type: 'GET',
        dataType: 'json',
        beforeSend: function(){
          $('div[data-at-row="main"]').css({'opacity':'0.2'});
          $('body').append(preloader);
          $('.aecid-preloader').show();
        },
        success: function(data){
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
            type:'excel',
            fileName: 'intervenciones.' + utc,
            worksheetName: 'Intervenciones'
          });
        }
      });

    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });

  /*Exportar Pdf Simplificado*/
  $('.inter-export-simple-pdf-btn').click(function(event) {
    inter_items_export = '';
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
        var nid = $('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:last-child .inter-item').attr('nid');
        inter_items_export += nid + ',';
      }
    }

    if(checked_inter_count > 0){
      $.ajax({
        url: '/intranet/intervenciones/'+inter_items_export+'/export',
        type: 'GET',
        dataType: 'json',
        beforeSend: function(){
          $('div[data-at-row="main"]').css({'opacity':'0.2'});
          $('body').append(preloader);
          $('.aecid-preloader').show();
        },
        success: function(data){
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
              format: 'a4',
              margins: {left:10, right:10, top:10, bottom:10},
              orientation: 'l',
              autotable: {
                tableWidth: '100%',
                styles: {
                  overflow: 'linebreak',
                  fontSize: 12
                },
                headerStyles: {
                  fillColor: [0, 0, 0],
                  textColor: 255,
                  fontStyle: 'bold',
                  halign: 'center'
                }
              }
            },
            fileName: 'intervenciones.' + utc,
            htmlContent: true,
            worksheetName: 'Intervenciones'
          });
        }
      });

    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });

  $('.controles-intervencion .inter-bnt-aprobar').click(function(){
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
      }
    }

    if(checked_inter_count > 0){
      $('select[data-drupal-selector="edit-action"]').val('change_moderation_state_aprobado');
      $('.view-gestion-de-interveciones form').submit();
    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });

  /*Intervención Vista - Botón Desaprobar*/
  $('.controles-intervencion .inter-bnt-desaporbar').click(function(){
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
      }
    }

    if(checked_inter_count > 0){
      $('select[data-drupal-selector="edit-action"]').val('change_moderation_state_desaprobado');
      $('.view-gestion-de-interveciones form').submit();
    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });

  /*Intervención Vista - Botón Eliminar*/
  $('.controles-intervencion .inter-bnt-eliminar').click(function(){
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
      }
    }

    if(checked_inter_count > 0){
      var retVal = confirm("¿Esta seguro de eliminar los Items seleccionados?");
      if( retVal == true ){
        $('select[data-drupal-selector="edit-action"]').val('change_moderation_state_eliminar');
        $('body.path-intranet-intervenciones .view-gestion-de-interveciones form#views-form-gestion-de-interveciones-page-1').submit();
        return true;
      }
      else{
        //console.log("Se cancelo la eliminación");
      }
    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });

  /*Intervención Vista - Botón Publicar*/
  $('.controles-intervencion .inter-bnt-publicar').click(function(){
    checked_inter_count = 0;

    for(var i = 0; i < $('.view-gestion-de-interveciones form table tbody tr').length; i++){
      if($('.view-gestion-de-interveciones form table tbody tr').eq(i).find('td:nth-child(1) div input[type="checkbox"].form-checkbox').is(':checked')){
        checked_inter_count++;
      }
    }

    if(checked_inter_count > 0){
      $('select[data-drupal-selector="edit-action"]').val('change_moderation_state_publicado');
      $('.view-gestion-de-interveciones form').submit();
    }else{
      alert('No has seleccionado ningún Item.');
    }

    return false;
  });


  /*********************
  Banner Front Page
  *********************/
  $('div[data-at-row="navbar"]').append('<div class="b-prev"></div><div class="b-next"></div>');
  var num_banner_slides = $('div[data-at-row="navbar"] .view.view-banner-portada > .view-content > .views-row').length;
  var _count = 0;

  setInterval(function(){
    if(_count < (num_banner_slides - 1)){
      _count ++;
    }else{
      _count = 0;
    }
    $('div[data-at-row="navbar"] .view.view-banner-portada > .view-content').css({'margin-left':-(_count * 100)+'%'});
  }, 5000);

  $('div[data-at-row="navbar"] .b-prev').click(function(event) {
    if(_count == 0){
      _count = (num_banner_slides - 1);
    }else{
      _count--;
    }
    $('div[data-at-row="navbar"] .view.view-banner-portada > .view-content').css({'margin-left':-(_count * 100)+'%'});
  });

  $('div[data-at-row="navbar"] .b-next').click(function(event) {
    if(_count < (num_banner_slides - 1)){
      _count ++;
    }else{
      _count = 0;
    }
    $('div[data-at-row="navbar"] .view.view-banner-portada > .view-content').css({'margin-left':-(_count * 100)+'%'});
  });

  /*Form Contact*/
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form input[data-drupal-selector="edit-name"]').attr('placeholder', 'Nombre *');
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form input[data-drupal-selector="edit-institucion"]').attr('placeholder', 'Institución');
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form input[data-drupal-selector="edit-email"]').attr('placeholder', 'E-mail *');
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form input[data-drupal-selector="edit-telefono"]').attr('placeholder', 'Teléfono');
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form input[data-drupal-selector="edit-subject"]').attr('placeholder', 'Asunto *');
  $('body.path-webform div[data-at-row="main"] .region main.block-main-content form.webform-submission-contact-form textarea[data-drupal-selector="edit-message"]').attr('placeholder', 'Mensaje *');

  /*********************
  Iconos Accesos
  *********************/
  for(var i = 0; i < $('body.path-frontpage div[data-at-row="features"] .region > .block').length; i++){
    var _u = $('body.path-frontpage div[data-at-row="features"] .region > .block').eq(i).find('.block__content .field-name-field-link-acceso-portada .field__item a').attr('href');
    $('body.path-frontpage div[data-at-row="features"] .region > .block').eq(i).find('.block__content .field-name-field-icono-acceso-portada > figure').attr('u',_u);
  }
  for(var i = 0; i < $('body.path-frontpage div[data-at-row="header"] .region > .block').length; i++){
    var _u = $('body.path-frontpage div[data-at-row="header"] .region > .block').eq(i).find('.block__content .field-name-field-link-acceso-portada .field__item a').attr('href');
    $('body.path-frontpage div[data-at-row="header"] .region > .block').eq(i).find('.block__content .field-name-field-icono-acceso-portada > figure').attr('u',_u);
  }
  for(var i = 0; i < $('div[data-at-row="subfeatures"] .region > .block').length; i++){
    var _u = $('div[data-at-row="subfeatures"] .region > .block').eq(i).find('.block__content .field-name-field-des-acceso-pie-pag .field__item a').attr('href');
    $('div[data-at-row="subfeatures"] .region > .block').eq(i).find('.block__content .field-name-field-icono-acceso-pie-pagina > figure').attr('u',_u);
  }
  $('body.path-frontpage div[data-at-row="features"] .region .block .block__content .field-name-field-icono-acceso-portada > figure').click(function(event) {
    var _str = $(this).attr('u');
    if(_str.search("https") != -1 || _str.search("http") != -1){
      window.open(_str,'_blank');
    }else{
      window.location.href = _str;
    }
  });
  $('body.path-frontpage div[data-at-row="header"] .region .block .block__content .field-name-field-icono-acceso-portada > figure').click(function(event) {
    var _str = $(this).attr('u');
    if(_str.search("https") != -1 || _str.search("http") != -1){
      window.open(_str,'_blank');
    }else{
      window.location.href = _str;
    }
  });
  $('div[data-at-row="subfeatures"] .region .block .block__content .field-name-field-icono-acceso-pie-pagina > figure').click(function(event) {
    var _str = $(this).attr('u');
    if(_str.search("https") != -1 || _str.search("http") != -1){
      window.open(_str,'_blank');
    }else{
      window.location.href = _str;
    }
  });

  /*Mapeo*/
  $('#mapeo-regions-container .mapeo-all-reload').on('click', function(event) {
    window.location.reload();
    return false;
  });
  $.ajax({
    url: '/extranet/mapeo/map',
    type: 'GET',
    dataType: 'json',
    success: function(data){
      $('#mapeo-map-container').html(data);
      $('#mapeo-map-container').append('<div class="map-tooltip"></div>');
      $('#mapeo-regions-container table tbody tr td a').click(onClickListaMapaRegiones);
      onRollOverMapaMapeo();
      onClickMapaItemsRegions();
    }
  });

  /*Mapeo click list departamentos/provincias*/
  function onClickListaMapaRegiones() {
    if($(this).attr('href') && $(this).attr('href') != ''){
      $.ajax({
        url: $(this).attr('href'),
        cache: false,
        type: 'GET',
        dataType: 'json',
        beforeSend: function(){
          $('.path-extranet-mapeo div[data-at-row="main"]').css({'opacity':'0.2'});
          $('body').append(preloader);
          $('.aecid-preloader').show();
        },
        success: function(resp){
          $('.aecid-preloader').hide();
          $('.aecid-preloader').remove();
          $('.view-mapeo table.table.views-table tbody').html(resp[0]);
          $('#mapeo-regions-container').html(resp[1]);
          $('#mapeo-map-container').html(resp[2]);
          $('#mapeo-map-container').append('<div class="map-tooltip"></div>');
          $('.path-extranet-mapeo div[data-at-row="main"]').css({'opacity':'1'});
          $('#mapeo-regions-container .mapeo-all-reload').show();
          $('#mapeo-regions-container table tbody tr td a').click(onClickListaMapaProvincias);
          onRollOverMapaMapeo();
          onClickMapaItemsProvinces();
        },
      });
    }

    return false;
  }

  function onClickListaMapaProvincias() {
    var ip = $(this).attr('ip');
    var ti = $(this).attr('ti');

    console.log(ip + '|' + ti);
    $.ajax({
      url: '/extranet/mapeo/provincia/'+ip+'/'+ti+'/show',
      cache: false,
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $('.path-extranet-mapeo div[data-at-row="main"]').css({'opacity':'0.2'});
        $('body').append(preloader);
        $('.aecid-preloader').show();
      },
      success: function(resp){
        $('.aecid-preloader').hide();
        $('.aecid-preloader').remove();
        $('.view-mapeo table.table.views-table tbody').html(resp[0]);
        $('.path-extranet-mapeo div[data-at-row="main"]').css({'opacity':'1'});
        $('#mapeo-regions-container table tbody tr td a').click(onClickListaMapaProvincias);
        onClickMapaItemsProvinces();
      },
    });

    return false;
  }

  /*Mapeo roll over*/
  function onRollOverMapaMapeo(){
    $description = $('#mapeo-map-container .map-tooltip');
    $('#mapeo-regions-container table tbody tr').each(function(index, el) {
      $('#mapeo-map-container .st0[id="'+$(el).children().eq(0).attr('d')+'"]').attr('href', $(el).children().eq(0).find('a').attr('href'));
      $('#mapeo-map-container .st0[id="'+$(el).children().eq(0).attr('d')+'"]').attr('ip', $(el).children().eq(0).find('a').attr('ip'));
      $('#mapeo-map-container .st0[id="'+$(el).children().eq(0).attr('d')+'"]').attr('ti', $(el).children().eq(0).find('a').attr('ti'));
      if($(el).children().eq(0).attr('c') != '0'){
        $('#mapeo-map-container .st0[id="'+$(el).children().eq(0).attr('d')+'"]').addClass('stactive');
      }
    });

    $('#mapeo-map-container .st0').hover(function(e) {
      $description.html($(this).attr('id'));
      $description.addClass('active');
      /*var x = $(this).offset().left - 500;
      var y = $(this).offset().top - 750;
      $('#mapeo-map-container').append('<div class="map-tooltip">'+$(this).attr('id')+'</div>');
      $('#mapeo-map-container .map-tooltip').css({'left': x + 'px', 'top': y + 'px'});*/
      $(this).css({'fill':'#FFB80F'});
    }, function() {
      $description.removeClass('active');
      //$('#mapeo-map-container .map-tooltip').remove();
      $(this).css({'fill':'#c8c8c8'});
      $('#mapeo-map-container .st0.stactive').css({'fill':'#D80F39'});
    });

    $(document).on('mousemove', function(e){

      $description.css({
        left:  e.pageX - 300,
        top:   e.pageY - 750
      });

    });

    $('#mapeo-regions-container table tbody > tr td:nth-child(1)').hover(function() {

      $('#mapeo-map-container .st0[id="'+$(this).attr('d')+'"]').css({'fill':'#FFB80F'});
    }, function() {
      $('#mapeo-map-container .st0[id="'+$(this).attr('d')+'"]').css({'fill':'#c8c8c8'});
      $('#mapeo-map-container .st0.stactive').css({'fill':'#D80F39'});
    });
  }

  function onClickMapaItemsRegions(){
    $('#mapeo-map-container .st0').click(onClickListaMapaRegiones);
  }

  function onClickMapaItemsProvinces(){
    $('#mapeo-map-container .st0').click(onClickListaMapaProvincias);
  }
});
