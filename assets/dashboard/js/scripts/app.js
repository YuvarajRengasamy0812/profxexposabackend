(function ($) {
  "use strict";

  $(document).on("pjax:popstate", function () {
    $(document).one("pjax:end", function (event) {
      $(event.target)
        .find("script[data-exec-on-popstate]")
        .each(function () {
          $.globalEval(this.text || this.textContent || this.innerHTML || "");
        });
    });
  });

  window.app = {
    name: "smartend",
    version: "7.3.0",
    // for chart colors
    color: {
      primary: "#e91e63",
      accent: "#a88add",
      warn: "#fcc100",
      info: "#6887ff",
      success: "#6cc788",
      warning: "#f77a99",
      danger: "#f44455",
      white: "#ffffff",
      light: "#f1f2f3",
      dark: "#2e3e4e",
      black: "#2a2b3c",
    },
    setting: {
      theme: {
        primary: "primary",
        accent: "accent",
        warn: "warn",
      },
      color: {
        primary: "#e91e63",
        accent: "#a88add",
        warn: "#fcc100",
      },
      folded: false,
      boxed: false,
      container: false,
      themeID: 1,
      bg: "",
    },
  };

  var setting = "jqStorage-" + app.name + "-Setting",
    storage = $.localStorage;

  if (storage.isEmpty(setting)) {
    storage.set(setting, app.setting);
  } else {
    app.setting = storage.get(setting);
  }
  var v = window.location.search.substring(1).split("&");
  for (var i = 0; i < v.length; i++) {
    var n = v[i].split("=");
    app.setting[n[0]] =
      n[1] === "true" || n[1] === "false" ? n[1] === "true" : n[1];
    storage.set(setting, app.setting);
  }

  // init
  function setTheme() {
    $("body")
      .removeClass($("body").attr("ui-class"))
      .addClass(app.setting.bg)
      .attr("ui-class", app.setting.bg);
    app.setting.folded
      ? $("#aside").addClass("folded")
      : $("#aside").removeClass("folded");
    app.setting.boxed
      ? $("body").addClass("container")
      : $("body").removeClass("container");

    $('.switcher input[value="' + app.setting.themeID + '"]').prop(
      "checked",
      true
    );
    $('.switcher input[value="' + app.setting.bg + '"]').prop("checked", true);

    $('[data-target="folded"] input').prop("checked", app.setting.folded);
    $('[data-target="boxed"] input').prop("checked", app.setting.boxed);
  }
  $(".folded-toggle").click(function () {
    if (app.setting.folded) {
      app.setting.folded = false;
      $("#aside").removeClass("folded");
    } else {
      app.setting.folded = true;
      $("#aside").addClass("folded");
    }
    storage.set(setting, app.setting);
    setTheme(app.setting);
  });

  // click to switch
  $(document).on("click.setting", ".switcher input", function () {
    var $this = $(this),
      $target;
    $target = $this.parent().attr("data-target")
      ? $this.parent().attr("data-target")
      : $this.parent().parent().attr("data-target");
    app.setting[$target] = $this.is(":checkbox")
      ? $this.prop("checked")
      : $(this).val();
    $(this).attr("name") === "color" &&
      (app.setting.theme = eval(
        "[" + $(this).parent().attr("data-value") + "]"
      )[0]) &&
      setColor();
    storage.set(setting, app.setting);
    setTheme(app.setting);
  });

  function setColor() {
    app.setting.color = {
      primary: getColor(app.setting.theme.primary),
      accent: getColor(app.setting.theme.accent),
      warn: getColor(app.setting.theme.warn),
    };
  }

  function getColor(name) {
    return app.color[name] ? app.color[name] : palette.find(name);
  }

  function init() {
    $("[ui-jp]").uiJp();
    $("body").uiInclude();
  }

  $(document).on("pjaxStart", function () {
    $("#aside").modal("hide");
    $("body").removeClass("modal-open").find(".modal-backdrop").remove();
    $(".navbar-toggleable-sm").collapse("hide");
  });

  function isResponsiveAside() {
    return window.matchMedia("(max-width: 1199.98px)").matches;
  }

  function markAsideBackdrop() {
    $("body").addClass("aside-open");
    $(".modal-backdrop").last().addClass("aside-backdrop");
  }

  function clearAsideBackdrop() {
    $("body").removeClass("aside-open modal-open-aside");
    $(".modal-backdrop.aside-backdrop").remove();
  }

  function syncResponsiveAside() {
    var $aside = $("#aside");

    if (isResponsiveAside()) {
      $aside.removeClass("folded active");
      if ($aside.hasClass("in")) {
        markAsideBackdrop();
      } else {
        clearAsideBackdrop();
      }
    } else {
      clearAsideBackdrop();
      $aside.removeClass("in").show();
      setTheme(app.setting);
    }
  }

  $(window).on("resize orientationchange", syncResponsiveAside);
  $(document).on("pjax:end", syncResponsiveAside);

  document.addEventListener(
    "click",
    function (event) {
      var trigger = event.target.closest ? event.target.closest('[data-toggle="modal"][data-target="#aside"]') : null;
      if (trigger && isResponsiveAside() && $("#aside").hasClass("in")) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        $("#aside").modal("hide");
      }
    },
    true
  );

  $("#aside")
    .on("shown.bs.modal", function () {
      markAsideBackdrop();
      syncResponsiveAside();
    })
    .on("hidden.bs.modal", function () {
      clearAsideBackdrop();
      syncResponsiveAside();
    })
    .on("click", function (event) {
      if (isResponsiveAside() && event.target === this) {
        $(this).modal("hide");
      }
    });

  $(document).on("click", "#aside .nav a[href]", function () {
    if (isResponsiveAside()) {
      $("#aside").modal("hide");
    }
  });
  init();
  setTheme();
  syncResponsiveAside();

  moment.locale("en", {
    week: {
      dow: first_day_of_week, // Monday is the first day of the week
    },
  });

  $('[data-toggle="tooltip"]').tooltip({ html: true });
})(jQuery);
