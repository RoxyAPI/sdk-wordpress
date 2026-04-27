/*
 * RoxyAPI admin script.
 *
 * Wires the Settings page interactions:
 *   1. Test Connection button: calls /roxyapi/v1/test-key via wp.apiFetch and
 *      surfaces an inline banner above the shortcode section on success or a
 *      friendly inline error on failure. Smooth-scrolls to the shortcode list
 *      after a successful test.
 *   2. Copy buttons next to each shortcode sample: writes the code to the
 *      clipboard via navigator.clipboard.writeText and flashes "Copied" on
 *      the button for two seconds.
 *
 * No inline scripts. No jQuery. Strings come from wp_localize_script via the
 * RoxyAPIAdmin global so they can be translated server side.
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    wireTestConnection();
    wireCopyButtons();
  });

  function getStrings() {
    var defaults = {
      testing: "Testing...",
      connected: "Connected.",
      connectedBanner:
        "Connected to Roxy. Paste a shortcode below to render a reading on any page.",
      noKey: "Paste your API key in the field above and save before testing.",
      invalidKey:
        "That key was rejected. Double check it on your roxyapi.com dashboard, paste it again, and save.",
      requestFailed: "Connection test failed. Try again in a moment.",
      copied: "Copied",
      copyFailed: "Copy failed",
      copy: "Copy",
    };
    var injected = window.RoxyAPIAdmin && window.RoxyAPIAdmin.strings;
    if (!injected) {
      return defaults;
    }
    var out = {};
    Object.keys(defaults).forEach(function (k) {
      out[k] =
        typeof injected[k] === "string" && injected[k].length > 0
          ? injected[k]
          : defaults[k];
    });
    return out;
  }

  function wireTestConnection() {
    var buttons = document.querySelectorAll("[data-roxyapi-test-connection]");
    if (!buttons.length) {
      return;
    }
    var strings = getStrings();
    buttons.forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        runTestConnection(button, strings);
      });
    });
  }

  function runTestConnection(button, strings) {
    var result = button.parentNode
      ? button.parentNode.querySelector(".roxyapi-test-connection-result")
      : null;
    var banner = document.getElementById("roxyapi-test-banner");
    setResult(result, strings.testing, "");
    hideBanner(banner);

    if (!window.wp || !window.wp.apiFetch) {
      setResult(result, strings.requestFailed, "is-error");
      return;
    }

    wp.apiFetch({ path: "/roxyapi/v1/test-key", method: "GET" })
      .then(function (data) {
        if (data && data.ok) {
          setResult(result, strings.connected, "is-success");
          showBanner(banner, strings.connectedBanner, "is-success");
          scrollToShortcodes();
          return;
        }
        var msg = humaniseError(data && data.message, strings);
        setResult(result, msg, "is-error");
        showBanner(banner, msg, "is-error");
      })
      .catch(function (err) {
        var msg = humaniseError(err && err.message, strings);
        setResult(result, msg, "is-error");
        showBanner(banner, msg, "is-error");
      });
  }

  function humaniseError(raw, strings) {
    if (!raw) {
      return strings.requestFailed;
    }
    var text = String(raw);
    if (/no api key configured/i.test(text)) {
      return strings.noKey;
    }
    if (/401|403|unauthor|forbidden|invalid/i.test(text)) {
      return strings.invalidKey;
    }
    return text;
  }

  function setResult(el, text, className) {
    if (!el) {
      return;
    }
    el.textContent = text;
    el.className =
      "roxyapi-test-connection-result" + (className ? " " + className : "");
  }

  function showBanner(banner, text, variant) {
    if (!banner) {
      return;
    }
    banner.textContent = text;
    banner.className =
      "roxyapi-test-banner is-visible" +
      (variant === "is-error" ? " is-error" : "");
  }

  function hideBanner(banner) {
    if (!banner) {
      return;
    }
    banner.textContent = "";
    banner.className = "roxyapi-test-banner";
  }

  function scrollToShortcodes() {
    var target = document.getElementById("roxyapi-shortcode-section");
    if (!target || typeof target.scrollIntoView !== "function") {
      return;
    }
    try {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    } catch (e) {
      target.scrollIntoView();
    }
  }

  function wireCopyButtons() {
    var strings = getStrings();
    document.querySelectorAll("[data-roxyapi-copy]").forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        var code = button.getAttribute("data-roxyapi-copy") || "";
        if (!code) {
          return;
        }
        copyText(code)
          .then(function () {
            flashButton(button, strings.copied, strings.copy, "is-copied");
          })
          .catch(function () {
            flashButton(button, strings.copyFailed, strings.copy, "");
          });
      });
    });
  }

  function copyText(text) {
    if (
      window.navigator &&
      window.navigator.clipboard &&
      window.navigator.clipboard.writeText
    ) {
      return window.navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      try {
        var textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "absolute";
        textarea.style.left = "-9999px";
        document.body.appendChild(textarea);
        textarea.select();
        var ok = document.execCommand("copy");
        document.body.removeChild(textarea);
        if (ok) {
          resolve();
        } else {
          reject(new Error("execCommand failed"));
        }
      } catch (e) {
        reject(e);
      }
    });
  }

  function flashButton(button, activeLabel, restoreLabel, activeClass) {
    var original = button.textContent;
    button.textContent = activeLabel;
    if (activeClass) {
      button.classList.add(activeClass);
    }
    setTimeout(function () {
      button.textContent = restoreLabel || original;
      if (activeClass) {
        button.classList.remove(activeClass);
      }
    }, 2000);
  }
})();
