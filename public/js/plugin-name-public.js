( function($) {
  $(document).ready(function() {
    $(".epv-sidebar-layout-container").parents("#main").css({"margin-top": 0, "padding-top": 0});
    function replace_price(obj) {
      obj.$element.val(accounting.formatNumber(obj.value, 2, ",", "."));
    }

    if ($('#gift_card_sale_form').length > 0) {
      $('#epv-sell-price').parsley().on('field:validate', function() {
        replace_price(this);
      });
      $("#epv-balance").parsley().on('field:validate', function() {
        replace_price(this);
      });
      $("#epv-sell-price").parsley().on("field:success", function() {
        var percent = pve.commission,
          sellPrice = this.value;

        var earnings = sellPrice * (percent/100);
        $(".calculated-amount").text(accounting.formatMoney(earnings));
      });
    }
    
    function unformat(val) {
        var invalid_chars = /[^0-9.,]/.test(val);
        if(invalid_chars) {
            return false;    
        }
        
        return val.replace(",", "");    
    }

    window.Parsley.addValidator("dollar", {
      requirementType: "string",
      validateString: function(val, requirement) {
        var comma_decimal = accounting.formatNumber(val, 2, ",", ".");
        var no_comma_decimal = accounting.formatNumber(val, 2, "", ".");
        var comma_no_decimal = accounting.formatNumber(val, 0, ",");
        var no_comma_no_decimal = accounting.formatNumber(val, 0, "");
        return comma_decimal == val ? true : no_comma_decimal == val ? true : comma_no_decimal == val ? true : no_comma_no_decimal == val ? true : false;
      },
      messages: {
        en: 'Please enter a valid dollar amount.'
      }
    }).addValidator("fmin", {
        requirementType: "string",
        validateString: function(val, requirement) {
            var unformatted = unformat(val);
            if(!unformatted || unformatted < requirement) {
                return false;    
            }
          
            return true;
        },
        messages: {
            en: 'Please enter a number greater than or equal to %s'    
        }
    }).addValidator("fmax", {
        requirementType: "string",
        validateString: function(val, requirement) {
            var unformatted = unformat(val);
            if( !unformatted || unformatted > requirement) {
                return false;    
            }
            
            return true;
        },
        messages: {
            en: 'Please enter a number less than or equal to %s'    
        }
    }).addValidator("flt", {
        requirement: "string",
        validateString: function(val, element) {
            var compareTo = $(element).val();
            val = val.replace(",", "");
            compareTo = compareTo.replace(",", "");
            if(parseFloat(val) > parseFloat(compareTo)) {
                return false;    
            } 
            return true;
        },
        messages: {
            en: "Enter a number less than your current balance."    
        }
    });

    //Start listing form ajax processing.
    $("#gift_card_sale_form").submit(function(e) {
      e.preventDefault();
      var data = {'action': 'save_gc_sale', 'form_id': 'gift_card_sale_form', 'url' : window.location.href};
      var fieldsData = $(this).serializeArray();
      var fields = {};
      
      for(i = 0; i < fieldsData.length; i++) {
        var field = fieldsData[i];
        var name = field.name;
        var value = field.value;
        fields[name] = value;
      }

      var brandName = document.getElementById("epv-brand-name");
      var brandCat = brandName.options[brandName.selectedIndex].dataset.onlineCard;
      if(Number(brandCat)) {
        fields['epv-virtual'] = "yes";
      } else {
        fields['epv-virtual'] = "no";
      }
      data.fields = fields;
      fields = false;

     $(this).closest(".epv-sell-main-container").addClass("processing-form");
      $.ajax({
        url: pve.ajaxUrl,
        type: 'post',
        data: data,
        success: function(response) {
          var formID = "#" + response.data.form_id;
          console.log(response);

          if (response.success === true) {
              if (response.data.redirect) {
                  window.location.href = response.data.redirect;
              }
            
              if(response.data.login) {
              	$(".trigger-epv-login-lb").click();
              	$.featherlight.defaults.beforeClose = function(event) {
              	    if($(formID).length > 0) {
              			$(formID).removeClass("epv-hidden")
              			.prop("disabled", false);
              		}
              	}
              }
            
              //The markup for the vendor sign-up form should be returned and displayed.
              $(formID).addClass("epv-hidden")
              .prop('disabled', true)
              .closest(".epv-sell-main-container")
              .removeClass('processing-form');
            
              } else {
                var string;
                if(response.data.message) {
              	    string = "<p style='font-size: 18px;'>" + response.data.message + "</p>";
                }
            
                if(response.data.failedFields) {
                  var failed = response.data.failedFields;
                  	string = "<p class='epv-form-error-message'>There was something wrong with your entry. Please check that these fields are correct and try again</p>";
                  	for(i=0; i<failed.length; i++) {
                  		$("#" + failed[i]).addClass("epv-field-error");
                  		$("#" + failed[i]).on('focus', function(e) {
                  		    $(this).removeClass("epv-field-error");
                  		    if($(".epv-field-error").length === 0) {
                  		       $(".epv-form-error-message").addClass("epv-hidden");
                  		    }
                  		});
                  	}
                }
            
                $(formID).closest(".epv-sell-main-container").removeClass('processing-form');
                $(formID).prop('disabled', false).removeClass("epv-hidden");
                if($(".epv-form-error-message").length > 0) {
                  $(".epv-form-error-message").removeClass("epv-hidden");
                } else {
                    $(formID).before(string);
                }
              }
         }
      }); //ajax post

      return false;

    }); //gift card sale submit
    
    $(".sort-gc-brands-wrapper .dropdown-toggle").on("click", function() {
        var classTarget = $(this).siblings(".alpha-dropdown-wrapper");
        if(classTarget.hasClass("open")) {
            classTarget.removeClass("open");
        } else {
            classTarget.addClass("open");    
        }
    });
    
    if(typeof $("#remove-card").colorbox == "function") {
        $("#remove-card").colorbox({inline:true, width: "40%"});    
    }
    $("#remove-card").click(function(event) {
        var cardId = $(event.target).closest("tr").data("cardId");
        $("#delete-listing").attr("data-cardid", cardId).attr("data-cardrow", $(this).closest("tr").data("row"));
    });
    
    $("#delete-listing").click(function(event) {
        event.preventDefault();
        $.colorbox.close();
        $(this).closest("tr").addClass("greyed-out");
        
        var data = {"action": "seller_removed_card", "card": $(this).data("cardid"), "row": $(this).data("cardrow") };
        $.ajax({
            url: pve.ajaxUrl,
            type: 'post',
            data: data,
            success: function(response) {
                if(response.success) {
                    var row = ".row-" + response.data.row;
                    $(row).remove();
                } else {
                    $(".epv-listings-chart").append("<p class='card-deletion-error' style='color: red; font-size: 16px; font-weight: bold;'>Unable to delete your card. Please try logging back in, or <a href='/contact-us'>contact</a> our support.</p>");
                }
            },
            complete: function(ajax, status) {
                window.location.reload();
            }
        });
    });
    
    function switchBrandImages() {
        var brandSel = document.getElementById("epv-brand-name");
        var currOptn = brandSel.options[brandSel.selectedIndex];
        var currImg = currOptn.dataset.brandImg;
        var currBrand = currOptn.value;
        var imgString = "<img src='" + currImg + "' alt='" + currBrand + "'>";
        $(".widget_epv_sidebar_widget .brand-image").html(imgString);
    }
    if(/sell-gift-cards/.test(window.location.pathname)) {
        switchBrandImages();
    }

    if(typeof $("#seller-fees-link").colorbox == "function") {
        $("#seller-fees-link").colorbox({inline: true});   
    }
    if($(".brands-select").length > 0) {
        $(".brands-select").change(function(e) {
            switchBrandImages();
            var isElectronic = e.target.options[e.target.selectedIndex].dataset.onlineCard;
            var isNum = /^\d+$/.test(isElectronic);
            if(isNum) {
                if(isElectronic == 1) {
                    $(".why-serial-numbers").css({"display": "block"});
                } 
                
                if(isElectronic == 0) {
                    $(".why-serial-numbers").css({"display": "none"});    
                }    
            }
        });
    }
    
    
    // Part of a failed implementation, but still a good interface for future use
    // (function() {
    //     if($(".resend-sn").length > 0) {
    //         $(".resend-sn").click(function(e) {
    //             e.preventDefault();
    //             $(this).attr("disabled", "true");
    //             var data = {"action": "resendgc", "order_id": $(this).parent().data("orderId")};
    //             $.ajax({
    //                 url: pve.ajaxUrl,
    //                 type: 'post',
    //                 data: data,
    //                 success: function(response) {
    //                     console.log(response);
    //                     var targetRow = ".order-" + response.data.order_id;
    //                     if(response.success === true) {
    //                         $(targetRow).find(".resend-sn").text("Sent").attr("disabled", true);
    //                         $(targetRow).find(".order-actions").append("<div class='email-sent'>Email Sent</div>");
    //                         $(".email-sent").fadeIn(500).delay(800).fadeOut(500, function() {
    //                             $(this).remove()
    //                         });
    //                     } else {
    //                         $(targetRow).find(".resend-sn").attr("disabled", "true");
    //                         $(".my_account_orders").before(response.data.error);
    //                     }    
    //                 }
    //             });
    //         });    
    //     }
    // })();
    
  }); //document ready
})(jQuery);
