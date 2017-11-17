class Framework {
  	constructor(options, callback, noIntro) {
      this.version = "v1.4.5";
    	this.config = JSON.parse(options);
      this.hidden = false;
      this.callback = callback;
      this.md = new MobileDetect(window.navigator.userAgent);

      this._initEvent();

      //with mobile
      if(this.md.mobile()) {
          if(!noIntro) this.showIntro();
      }
      else{

        if(this.config.mobile_only) { 
          this.showWarning();
        }
        else{
          if(!noIntro) this.showIntro();
        }

      }
   	}
  	
    //xiti  - analytics stat
  	track(pageName, action, isClick) {
  		
      //xiti
      if(isClick) {
        ATTag.click.send({
          level2: '10',
          chapter1: "Webprod",
          chapter2:"60Secondes",
          chapter3: this.config.projectName, //facultatif
          name: pageName,
          type: "action",
          customVars: {
            site: {
              1 : this.config.language,
              3 : encodeURIComponent(document.location.href)
            }
          },
          customObject: {
            device : (function(){var bp = {s:600,d:1000};var wojd="error";if (typeof window.innerWidth!='undefined'){var w=window.innerWidth;if(w<bp.s){wojd="smartphone";}else if(w<bp.d) {wojd="tablet";}else if(w>=bp.d){wojd="desktop";}else{wojd="error";}};return wojd;})()
          }
        })
      }
      else{
        ATTag.page.send({
          level2: '10',
          chapter1: "Webprod",
          chapter2:"60Secondes",
          chapter3: this.config.projectName, //facultatif
          name: pageName, //obligatoire
          customVars: {
            site: {
              1 : this.config.language,
              3 : encodeURIComponent(document.location.href)
            }
          },
          customObject: {
            device : (function(){var bp = {s:600,d:1000};var wojd="error";if (typeof window.innerWidth!='undefined'){var w=window.innerWidth;if(w<bp.s){wojd="smartphone";}else if(w<bp.d) {wojd="tablet";}else if(w>=bp.d){wojd="desktop";}else{wojd="error";}};return wojd;})()
          }
        });
      }

      //analytics
  		for(var i=0;i<this.config.analytics.length;i++) {	
        ga(this.config.analytics[i].name + '.send', {
          hitType: 'event',
          eventCategory: "NAME=" + this.config.projectName,
          eventAction: pageName,
          eventLabel: action
        });
      }
  	}

    showIntro() {
      
      this.track("landing page", "show-landing-page-to-user");

      var collab;

      switch(this.config.language) {
        case "fr":
        collab = 'EN COLLABORATION AVEC';
        break;

        case "en":
        collab = 'IN COLLABORATION WITH';
        break;

        case "de":
        collab = 'IN ZUSAMMENARBEIT MIT';
        break;
      }

      var $page = "<div id='onf-intro'>";
      
      $page += "<div class='onf-wrapper'>";
      $page += "<div class='partners'>";
      $page += "<div class='arte'></div>";
      $page += "<div class='onf'></div>";
      $page += "</div>";

      $page += "<div class='collab'>";
      $page += "<div class='collab-text'>"+collab+"</div>";
      $page += "<div class='idfa'></div>";
      $page += "</div>";
      
      $page += "</div";
      $page += "</div>";
      $page = $($page);

      $page.prependTo($("body"))
              .delay(5000)
              .fadeOut(600, (function(){
                  $(this).remove();

                  if(this.callback != null) this.callback.apply(null);
              }).bind(this));

      //animation onf-arte
      $page.find(".onf-wrapper").delay(200).animate({opacity: 1}, 600)
    }

    showWarning() {
      this.track("page sms", "show-page-to-desktop-user");

      var $page = "<div id='onf-warning'>";
      
      //maincontent
      $page += "<div class='warning-content'>";
      
      //warning
      $page += "<div class='warning-mobile'>";
      $page += this.config.landing.warning[this.config.language]
      $page += "</div>";

      if(this.config.landing.misc[this.config.language] !== "") {
       
        //tel block
        $page += "<div class='warning-phone-block'>";
        $page += "<input type='tel' id='warning-phone'>";
        $page += "<button id='warning-submit'>OK</button>";
        
        //error block
        var merci, oups;
        switch(this.config.language) {
          case "fr":
          merci = "Merci!";
          oups = "Numéro invalide"
          break;

          case "de":
          merci = "Merci de!";
          oups = "Numéro invalide de"
          break;

          case "en":
          merci = "Thank you!";
          oups = "Invalid Number"
          break;
        }

        $page += "<div class='warning-status-block'>";
        $page += "<span class='valid hide'>" + merci + "</span>"
        $page += "<span class='error hide'>" + oups + "</span>"
        $page += "</div>";


        $page += "<div class='warning-misc'>"+this.config.landing.misc[this.config.language]+"</div>";
        $page += "</div>";
      }

      //project block
      var smallMargin = (this.config.landing.misc[this.config.language] === "") ? "class='nophone'" : "";
      var toolong = (this.config.projectName.toUpperCase() == "PIGEON VOYAGEUR" || this.config.projectName.toUpperCase() == "STIR" || this.config.projectName.toUpperCase() == "A TEMPORARY CONTACT") ? " toolong" : "";

      $page += "<hr " +smallMargin+ ">";
      $page += "<div class='warning-project'>";
      $page += "<div class='warning-project-title'>"+this.config.landing.title[this.config.language]+"</div>";
      $page += "<div class='warning-project-author'>"+this.config.landing.author[this.config.language]+"</div>";
      $page += "<div class='warning-project-tagline"+toolong+"'>"+this.config.landing.tagline[this.config.language]+"</div>";
      $page += "</div>";

     

      //end //maincontent
      $page += "</div>";

      $page += "</div>";

      $page = $($page);
      $page.prependTo($("body"));

      $("#warning-submit").on("click", this.onSendTexto.bind(this));
    }

    onSendTexto() {

      this.track("page sms", "click-send-sms-validating");

      var warning = $("#onf-warning")
      , phone = $("#warning-phone")
      , sms = this.config.landing.sms[this.config.language]
      , number = phone.intlTelInput("getNumber")
      , isValid = phone.intlTelInput("isValidNumber");

      if(number != "" && isValid) {
        
        $(".warning-status-block .error").addClass("hide");

        $.ajax({
          url: "https://veryveryshort.nfb.ca/api/send-texto",
          data: {
            number: number,
            sms: sms
          },
          method: "POST",
          success: (function(data) {
            if(data === "sent") {
              phone.val('');

              var valid = $(".warning-status-block .valid");
              valid.removeClass("hide");
              setTimeout(valid.addClass.bind(valid), 2000, "hide");

              this.track("page sms", "click-send-sms-sent");
            }
          }).bind(this)
        });
      }
      else{
        //show error
        $(".warning-status-block .error").removeClass("hide");
      }
    }

    _initEvent() {

      $("body").on("touchmove", (function(e){
        
        if(!this.hidden) {

          $(".hh_onf").addClass("hh_hidden")
            .on("animationend", function(e){
              $(e.target).hide();
            });

          this.hidden = true;
        }

      }).bind(this));
    }
}

//ready doc
$( document ).ready(function() {
  
  var phone = $("#warning-phone")
  , submit = $("#warning-submit");

  phone.intlTelInput({utilsScript: "/common/js/utils.js"});

  var countryBtn = $("<button id='warning-country'>US</button>");

  //add custom button
  $(".selected-flag").parent().parent().append(countryBtn);

  //open dropdown
  countryBtn.on("click", function(e){
    $(".selected-flag").trigger(e)
  });

  //change country
  phone.on("countrychange", function(e, countryData) {
      countryBtn.html(countryData.iso2.toUpperCase());
  });
});