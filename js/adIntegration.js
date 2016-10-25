/**
 * @file
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.place_ads = {
    attach: function (context, settings) {
      setTags(settings);
    }
  };

  function setTags(settings) {
    if (typeof TFM == 'undefined') {
      return;
    }

    for (var slot_html_id in settings.AdvertisingSlots) {
      if (settings.AdvertisingSlots.hasOwnProperty(slot_html_id)) {
        var tag = settings.AdvertisingSlots[slot_html_id][window.adsc_device];
        $('#' + slot_html_id).html('<script>' + window.TFM.Tag.getAdTag(tag, slot_html_id) + ';</script>');
      }
    }
  }

})(jQuery, Drupal);
