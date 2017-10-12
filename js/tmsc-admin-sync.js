jQuery(document).ready(($) => {

  // Admin code for the tms object sync UI
  const objectSyncButton = $('#tmsc-sync-button');
  const objectSyncForm = $('#tmsc-form');
  const loadingImg = $('#ajax-loading');
  const updatedTime = $('#tmsc-last-updated-value');
  const updatedTxt = $('#last-updated-text');

  // Our localized wp data.
  const adminConfig = window.tmscAdminData;

  setUIVisibility();

  // Override the form submit button
  objectSyncForm.submit((e) => {
    // Hide our sync UI and display core WP spinner
    objectSyncButton.hide();
    updatedTxt.hide();
    loadingImg.attr('style', 'visibility:visible');
    // Run our async Sync
    doSync();
    e.preventDefault();
  });

  /**
   * On page load, set the UI correctly based on Sync status.
   */
  function setUIVisibility() {
    if ('Syncing TMS Objects' === updatedTime.val()) {
      objectSyncButton.hide();
      updatedTxt.hide();
      loadingImg.attr('style', 'visibility:visible');
      setTimeout(pollSyncStatus, 5000);
    } else {
      objectSyncButton.show();
      updatedTxt.show();
      loadingImg.attr('style', 'visibility:hidden');
    }
  }

  /**
   * Our Promise wrapper for our ajax calls
   */
  function ajax(options) {
    return new Promise((response) => {
      $.ajax(options).done(response);
    });
  }

  /**
   * Update the thumbnail to the current value in the select
   */
  function doSync() {
    ajax({
      type: 'POST',
      url: adminConfig.wp_ajax_url,
      data: objectSyncForm.serialize(),
      dataType: 'json',
    })
    .then(pollSyncStatus);
  }

  /**
   * Query the options table for the sync status
   */
  function pollSyncStatus() {
    ajax({
      type: 'POST',
      url: adminConfig.wp_ajax_url,
      data: {
        action: 'get_option_value',
        nonce: adminConfig.wp_admin_nonce,
        option_key: 'tmsc-last-sync-date',
        old_value: updatedTime.val(),
      },
      dataType: 'json',
    })
    .then((response) => {
      if (response.success) {
        updatedTxt.text(response.data);
        updatedTxt.show();
        objectSyncButton.show();
        loadingImg.attr('style', 'visibility:hidden');
      } else {
        // Repoll for our update value.
        setTimeout(pollSyncStatus, 5000);
      }
    });
  }

  /**
   * Handle manual updates with sync-lock field
   */
  const publishButton = $('#publish');
  const synclockClear = $('#clear-sync-lock-status');
  if ( synclockClear.size() ) {
    console.log( synclockClear.size() );
    // Override the form submit button
    publishButton.on( 'click', (e) => {
      $('#sync_lock').val( 1 );
    });

    // Override the form submit button
    synclockClear.on( 'click', (e) => {
      $('#sync_lock').val( 0 );
      $('#post').submit();
      e.preventDefault();
    });
  }
});

