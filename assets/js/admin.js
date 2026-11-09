jQuery(function($){
 $(document).on('click','.sk-pick',function(e){e.preventDefault();var b=$(this),t=$(b.data('target')),multi=b.data('multi')==1;
  var f=wp.media({multiple:multi,library:{type:'image'}});
  f.on('select',function(){var s=f.state().get('selection');
   if(multi){var ids=[],h='';s.each(function(a){ids.push(a.id);var z=a.attributes.sizes;h+='<img src="'+(z&&z.thumbnail?z.thumbnail.url:a.attributes.url)+'">';});t.val(ids.join(','));b.siblings('.sk-thumbs').html(h);}
   else{var a=s.first().toJSON();t.val(a.url);b.siblings('.sk-thumbs').html('<img src="'+a.url+'">');}});
  f.open();});
 $(document).on('click','.sk-clear',function(e){e.preventDefault();$($(this).data('target')).val('');$(this).siblings('.sk-thumbs').empty();});
});
jQuery(function($){
 $(document).on('click','.sk-rimg',function(e){e.preventDefault();var r=$(this).closest('.sk-row'),f=wp.media({multiple:false,library:{type:'image'}});
  f.on('select',function(){var u=f.state().get('selection').first().toJSON().url;r.find('.sk-rimg-url').val(u);r.find('.sk-rprev').css('background-image','url('+u+')').text('');});f.open();});
 $(document).on('click','.sk-rclr',function(e){e.preventDefault();var r=$(this).closest('.sk-row');r.find('.sk-rimg-url').val('');r.find('.sk-rprev').css('background-image','none').text('Görsel yok');});
 $(document).on('click','.sk-rsave',function(e){e.preventDefault();var b=$(this),r=b.closest('.sk-row');b.prop('disabled',true);
  $.post(SKA.ajax,{action:'sk_card_save',nonce:SKA.nonce,id:r.data('id'),kind:r.data('kind'),title:r.find('.sk-rtitle').val(),text:r.find('.sk-rtext').val(),img:r.find('.sk-rimg-url').val()},function(res){b.prop('disabled',false);r.find('.sk-rmsg').text(res&&res.success?'Kaydedildi ✓':'Hata (başlık boş olamaz)').stop(true).fadeIn().delay(2000).fadeOut();});});
});
