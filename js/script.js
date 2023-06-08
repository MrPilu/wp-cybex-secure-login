(function ($) {
  $(document).ready(function () {
    // for Contact Form option
    $("#restrict-sending-emails")
      .change(function () {
        if ($(this).is(":checked")) {
          $(".contact-form-checked").show();
        } else {
          $(".contact-form-checked").hide();
        }
      })
      .trigger("change");

    /* hide zero values */
    $(".cbxsec-zero-value").addClass("cbxsec_hidden");
    /* hide "block/add to denylist" time options at the page load */
    $(".cbxsec-hidden-input, .cbxsec-display").toggleClass("cbxsec_hidden");

    /* display inputs if 'Edit' was clicked*/
    $("#cbxsec-time-of-lock-edit").click(function () {
      $("#cbxsec-time-of-lock-display, #cbxsec-time-of-lock").toggleClass(
        "cbxsec_hidden"
      );
    });
    $("#cbxsec-allowed-retries-edit").click(function () {
      $("#cbxsec-allowed-retries-display, #cbxsec-allowed-retries").toggleClass(
        "cbxsec_hidden"
      );
    });
    $("#cbxsec-time-to-reset-edit").click(function () {
      $("#cbxsec-time-to-reset-display, #cbxsec-time-to-reset").toggleClass(
        "cbxsec_hidden"
      );
    });
    $("#cbxsec-allowed-locks-edit").click(function () {
      $("#cbxsec-allowed-locks-display, #cbxsec-allowed-locks").toggleClass(
        "cbxsec_hidden"
      );
    });
    $("#cbxsec-time-to-reset-block-edit").click(function () {
      $(
        "#cbxsec-time-to-reset-block-display, #cbxsec-time-to-reset-block"
      ).toggleClass("cbxsec_hidden");
    });
    $("#cbxsec-time-interval-for-cntctfrm-edit").click(function () {
      $(
        "#cbxsec-time-interval-for-cntctfrm-display, #cbxsec-time-interval-for-cntctfrm"
      ).toggleClass("cbxsec_hidden");
    });

    /* write zero if input empty */
    $("[type = number]").on("change", function () {
      var $this = $(this);
      if ("" == $this.val()) {
        $this.val(0);
      }
    });

    /* time-of-lock */
    var daysOfLock = $("#cbxsec-days-of-lock-display").val(),
      hoursOfLock = $("#cbxsec-hours-of-lock-display").val(),
      minutesOfLock = $("#cbxsec-minutes-of-lock-display").val(),
      /* allowed-retries */
      allowedRetries = $("#cbxsec-allowed-retries-number-display").val(),
      /* time-to-reset */
      daysToReset = $("#cbxsec-days-to-reset-display").val(),
      hoursToReset = $("#cbxsec-hours-to-reset-display").val(),
      minutesToReset = $("#cbxsec-minutes-to-reset-display").val(),
      /* allowed-locks */
      allowedLocks = $("#cbxsec-allowed-locks-number-display").val(),
      /* time-to-reset-block */
      daysToResetBlock = $("#cbxsec-days-to-reset-block-display").val(),
      hoursToResetBlock = $("#cbxsec-hours-to-reset-block-display").val(),
      minutesToResetBlock = $("#cbxsec-minutes-to-reset-block-display").val(),
      /* time-interval-for-cntctfrm */
      daysTimeIntervalCntctfrm = $(
        "#cbxsec-days-time-interval-for-cntctfrm-display"
      ).val(),
      hoursTimeIntervalCntctfrm = $(
        "#cbxsec-hours-time-interval-for-cntctfrm-display"
      ).val(),
      minutesTimeIntervalCntctfrm = $(
        "#cbxsec-minutes-time-interval-for-cntctfrm-display"
      ).val(),
      secondsTimeIntervalCntctfrm = $(
        "#cbxsec-seconds-time-interval-for-cntctfrm-display"
      ).val();
    $(document).click(function (event) {
      /* hide time-of-lock inputs if clicked outside and values not changed */
      if (
        !$(event.target).closest(
          "#cbxsec-time-of-lock-edit, #cbxsec-time-of-lock"
        ).length &&
        daysOfLock == $("#cbxsec-days-of-lock-display").val() &&
        hoursOfLock == $("#cbxsec-hours-of-lock-display").val() &&
        minutesOfLock == $("#cbxsec-minutes-of-lock-display").val()
      ) {
        $("#cbxsec-time-of-lock-display").removeClass("cbxsec_hidden");
        $("#cbxsec-time-of-lock").addClass("cbxsec_hidden");
      }
      /* hide allowed-retries inputs if clicked outside and values not changed */
      if (
        !$(event.target).closest(
          "#cbxsec-allowed-retries-edit, #cbxsec-allowed-retries"
        ).length &&
        allowedRetries == $("#cbxsec-allowed-retries-number-display").val()
      ) {
        $("#cbxsec-allowed-retries-display").removeClass("cbxsec_hidden");
        $("#cbxsec-allowed-retries").addClass("cbxsec_hidden");
      }
      /* hide time-to-reset inputs if clicked outside and values not changed */
      if (
        !$(event.target).closest(
          "#cbxsec-time-to-reset-edit, #cbxsec-time-to-reset"
        ).length &&
        daysToReset == $("#cbxsec-days-to-reset-display").val() &&
        hoursToReset == $("#cbxsec-hours-to-reset-display").val() &&
        minutesToReset == $("#cbxsec-minutes-to-reset-display").val()
      ) {
        $("#cbxsec-time-to-reset-display").removeClass("cbxsec_hidden");
        $("#cbxsec-time-to-reset").addClass("cbxsec_hidden");
      }
      /* hide allowed-locks inputs if clicked outside and values not changed */
      if (
        !$(event.target).closest(
          "#cbxsec-allowed-locks-edit, #cbxsec-allowed-locks"
        ).length &&
        allowedLocks == $("#cbxsec-allowed-locks-number-display").val()
      ) {
        $("#cbxsec-allowed-locks-display").removeClass("cbxsec_hidden");
        $("#cbxsec-allowed-locks").addClass("cbxsec_hidden");
      }
      /* hide time-to-reset-block inputs if clicked outside and values not changed */
      if (
        !$(event.target).closest(
          "#cbxsec-time-to-reset-block-edit, #cbxsec-time-to-reset-block"
        ).length &&
        daysToResetBlock == $("#cbxsec-days-to-reset-block-display").val() &&
        hoursToResetBlock == $("#cbxsec-hours-to-reset-block-display").val() &&
        minutesToResetBlock == $("#cbxsec-minutes-to-reset-block-display").val()
      ) {
        $("#cbxsec-time-to-reset-block-display").removeClass("cbxsec_hidden");
        $("#cbxsec-time-to-reset-block").addClass("cbxsec_hidden");
      }
      if (
        !$(event.target).closest(
          "#cbxsec-time-interval-for-cntctfrm-edit, #cbxsec-time-interval-for-cntctfrm"
        ).length &&
        daysTimeIntervalCntctfrm ==
          $("#cbxsec-days-time-interval-for-cntctfrm-display").val() &&
        hoursTimeIntervalCntctfrm ==
          $("#cbxsec-hours-time-interval-for-cntctfrm-display").val() &&
        minutesTimeIntervalCntctfrm ==
          $("#cbxsec-minutes-time-interval-for-cntctfrm-display").val() &&
        secondsTimeIntervalCntctfrm ==
          $("#cbxsec-seconds-time-interval-for-cntctfrm-display").val()
      ) {
        $("#cbxsec-time-interval-for-cntctfrm-display").removeClass(
          "cbxsec_hidden"
        );
        $("#cbxsec-time-interval-for-cntctfrm").addClass("cbxsec_hidden");
      }
      event.stopPropagation();
    });

    $('select[name="cbxsec_user_email_address"]').on("focus", function () {
      $("#cbxsec_user_mailto").attr("checked", "checked");
    });
    $('input[name="cbxsec_email_address"]').on("focus", function () {
      $("#cbxsec_custom_mailto").attr("checked", "checked");
    });

    /* prevent form submit but get defaut text into form textarea */
    $('button[name="cbxsec_return_default"]').click(function (event) {
      var restore_type = $(this).val();
      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "cbxsec_restore_default_message",
          message_option_name: restore_type,
          cbxsec_nonce: cbxsecScriptVars.cbxsec_ajax_nonce,
        },
        success: function (result) {
          var data = $.parseJSON(result);
          /* add notice */
          $(".cbxsec-restore-default-message").remove();
          $(".updated, .error").hide();
          $("#cbx_save_settings_notice").after(data["admin_notice_message"]);

          $.each(data["restored_messages"], function (key, val) {
            name = "cbxsec_" + key;
            $('textarea[name="' + name + '"]').val(val);
          });
        },
      });
      event.preventDefault();
      return false;
    });

    $('input[name="cbxsec_add_to_allowlist_my_ip"]').click(function () {
      if ($(this).is(":checked"))
        $('input[name="cbxsec_add_to_allowlist"]')
          .val($('input[name="cbxsec_add_to_allowlist_my_ip_value"]').val())
          .attr("readonly", "readonly");
      else
        $('input[name="cbxsec_add_to_allowlist"]')
          .val("")
          .removeAttr("readonly");
    });
  });
})(jQuery);
