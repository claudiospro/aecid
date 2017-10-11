jQuery(document).ready(function($) {
  /*Preloader*/
  var preloader = '<div class="aecid-preloader">';
  preloader += '  <img src="/sites/default/files/aecid-preloader-intranet.svg" width="150" height="150">';
  preloader += '  <label>';
  //preloader += '    <h3>100%</h3>';
  preloader += '    <small>Completando...</small>';
  preloader += '  </label>';
  preloader += '</div>';

  $('.aecid-actor-subgroup-container .aecid-actor-subgroup .aecid-actor-subgroup-title').click(function(event) {
    $(this).parent().find('.aecid-actor-subgroup-data').toggle();
  });

  $('.aecid-actor-subgroup-container .aecid-actor-subgroup .aecid-actor-subgroup-data-name').click(loadDataLightBox);
  $('.aecid-actor-subgroup-container .aecid-actor-subgroup .aecid-actor-show-state').change(onChangeActorState);
  $('.aecid-actor-subgroup-container .aecid-actor-subgroup .aecid-actor-subgroup-data .aecid-actor-subgroup-data-alphabetic .aecid-actor-alphabetic-letter').click(onClickActorAlphabeticLetter);

  function loadDataLightBox(e){
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

        $('.aecid-intranet-lightbox .aecid-intranet-lightbox-tabs-list .aecid-intranet-lightbox-tab-button').click(changeTabPane);

        //Cierra lightbox
        $('.aecid-intranet-lightbox-close').click(closeLightBox);
      }
    });

    return false;
  }

  function onChangeActorState(e) {
    var state = 0;
    var nid = $(this).attr('name');
    if($(this).is(':checked')){
      state = 1;
    }else{
      state = 0;
    }
    $.ajax({
      url: '/intranet/actor_espanol/'+nid+'/'+state+'/updatestate',
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

        //Cierra lightbox
        $('.aecid-intranet-lightbox-close').click(closeLightBox);
      }
    });

    return false;
  }

  function onClickActorAlphabeticLetter(e) {
    $(this).parent().parent().parent().find('.aecid-actor-subgroup-data-alphabetic .aecid-actor-alphabetic-letter').removeClass('active');
    $(this).addClass('active');
    var letter_filter = $(this).text().toLowerCase();
    var data_table = $(this).parent().parent().parent().find('table tbody tr').length;
    if(letter_filter != 'todos'){
      $(this).parent().parent().parent().find('table tbody tr').hide();
      $(this).parent().parent().parent().find('table tbody tr[letter="'+letter_filter+'"]').show();
    }else{
      $(this).parent().parent().parent().find('table tbody tr').show();
    }
    //console.log($(this).text().toLowerCase());
    //console.log(data_table);
    return false;
  }

  function changeTabPane(e){
    var tabpane_index = $(this).attr('tabindex');
    $('.aecid-intranet-lightbox .aecid-intranet-lightbox-tabs-list .aecid-intranet-lightbox-tab-button').removeClass('aecid-intranet-lightbox-active-tab');
    $(this).addClass('aecid-intranet-lightbox-active-tab');
    $('.aecid-intranet-lightbox .aecid-intranet-lightbox-content-body .aecid-intranet-lightbox-tabs-pane .aecid-intranet-lightbox-group-tab').hide();
    $('.aecid-intranet-lightbox .aecid-intranet-lightbox-content-body .aecid-intranet-lightbox-tabs-pane').children().eq((tabpane_index - 1)).show();
  }

  function closeLightBox(e){
    $('.aecid-intranet-lightbox').hide();
    $('.aecid-intranet-lightbox').remove();
  }

});
