/*
 * RoxyAPI Shortcodes Library script.
 *
 * Pure vanilla. No jQuery. No build step. Powers four behaviours:
 *
 *   1. Search filter on card title, description, tag, and domain. Debounced
 *      150ms. Hides cards whose haystack does not match. Empty groups hide.
 *   2. Domain tabs. Click sets the active tab, hides non-matching groups.
 *      Updates the URL hash so refresh and back/forward preserve the view.
 *   3. Hash on load. Reads `#tab=tarot` etc. and activates that tab.
 *   4. Copy buttons. Same `data-roxyapi-copy` contract as admin.js. Flashes
 *      "Copied" for 1.5s using `navigator.clipboard.writeText`.
 *
 * Bonus: pressing `/` while not inside a form field focuses the search input,
 * matching common documentation site UX.
 */

(function () {
  "use strict";

  var SEARCH_DEBOUNCE_MS = 150;
  var COPY_FLASH_MS = 1500;

  document.addEventListener("DOMContentLoaded", function () {
    var page = document.querySelector(".roxyapi-shortcodes");
    if (!page) {
      return;
    }
    var strings = readStrings();
    wireTabs(page);
    wireSearch(page, strings);
    wireCopyButtons(page, strings);
    wireKeyboardShortcut(page);
    applyHashTabFromUrl(page);
  });

  function readStrings() {
    var defaults = {
      copied: "Copied",
      copyFailed: "Copy failed",
      copy: "Copy",
      noResults:
        "No shortcodes match. Try a different domain or clear the search.",
    };
    var injected = window.RoxyAPIShortcodes && window.RoxyAPIShortcodes.i18n;
    if (!injected) {
      return defaults;
    }
    var out = {};
    Object.keys(defaults).forEach(function (key) {
      out[key] =
        typeof injected[key] === "string" && injected[key].length > 0
          ? injected[key]
          : defaults[key];
    });
    return out;
  }

  function wireTabs(page) {
    var tabs = page.querySelectorAll(".roxyapi-library-tab");
    if (!tabs.length) {
      return;
    }
    tabs.forEach(function (tab) {
      tab.addEventListener("click", function (event) {
        event.preventDefault();
        var domain = tab.getAttribute("data-roxyapi-domain") || "all";
        activateTab(page, domain);
        writeHashTab(domain);
      });
    });
  }

  function activateTab(page, domain) {
    var tabs = page.querySelectorAll(".roxyapi-library-tab");
    tabs.forEach(function (tab) {
      var isMatch =
        (tab.getAttribute("data-roxyapi-domain") || "all") === domain;
      tab.classList.toggle("is-active", isMatch);
      tab.setAttribute("aria-selected", isMatch ? "true" : "false");
    });

    var groups = page.querySelectorAll(".roxyapi-library-group");
    groups.forEach(function (group) {
      var slug = group.getAttribute("data-roxyapi-group") || "";
      if (domain === "all" || domain === slug) {
        group.hidden = false;
      } else {
        group.hidden = true;
      }
    });

    // Re-apply the search filter so an active query still narrows results
    // inside the newly visible domain.
    applySearch(page);
  }

  function writeHashTab(domain) {
    try {
      if (domain === "all") {
        if (history.replaceState) {
          history.replaceState(
            null,
            "",
            window.location.pathname + window.location.search,
          );
        } else {
          window.location.hash = "";
        }
        return;
      }
      var newHash = "#tab=" + encodeURIComponent(domain);
      if (history.replaceState) {
        history.replaceState(null, "", newHash);
      } else {
        window.location.hash = newHash;
      }
    } catch (e) {
      // Ignore; hash sync is a nicety, not load-bearing.
    }
  }

  function applyHashTabFromUrl(page) {
    var hash = window.location.hash || "";
    var match = hash.match(/tab=([a-z0-9-]+)/i);
    if (!match) {
      return;
    }
    var domain = decodeURIComponent(match[1]);
    var tab = page.querySelector(
      '.roxyapi-library-tab[data-roxyapi-domain="' + cssEscape(domain) + '"]',
    );
    if (!tab) {
      return;
    }
    activateTab(page, domain);
  }

  function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === "function") {
      return window.CSS.escape(value);
    }
    return String(value).replace(/["\\]/g, "\\$&");
  }

  function wireSearch(page, strings) {
    var input = page.querySelector(".roxyapi-library-search");
    if (!input) {
      return;
    }
    var empty = page.querySelector(".roxyapi-library-empty");
    if (empty && strings.noResults) {
      empty.textContent = strings.noResults;
    }
    var timer = null;
    input.addEventListener("input", function () {
      if (timer) {
        clearTimeout(timer);
      }
      timer = setTimeout(function () {
        applySearch(page);
      }, SEARCH_DEBOUNCE_MS);
    });
  }

  function applySearch(page) {
    var input = page.querySelector(".roxyapi-library-search");
    var query = input ? input.value.trim().toLowerCase() : "";
    var groups = page.querySelectorAll(".roxyapi-library-group");
    var visibleGroupCount = 0;
    var totalVisibleCards = 0;

    groups.forEach(function (group) {
      // Respect the active tab: if a group is hidden by the tab choice,
      // leave it hidden regardless of the search.
      if (group.hidden) {
        return;
      }
      var cards = group.querySelectorAll(".roxyapi-library-card");
      var visibleInGroup = 0;
      cards.forEach(function (card) {
        var haystack = (
          card.getAttribute("data-roxyapi-tags") || ""
        ).toLowerCase();
        var matches = query === "" || haystack.indexOf(query) !== -1;
        card.hidden = !matches;
        if (matches) {
          visibleInGroup += 1;
        }
      });
      if (visibleInGroup === 0 && query !== "") {
        group.hidden = true;
      } else {
        visibleGroupCount += 1;
        totalVisibleCards += visibleInGroup;
      }
    });

    var empty = page.querySelector(".roxyapi-library-empty");
    if (empty) {
      var hasResults =
        totalVisibleCards > 0 || (query === "" && visibleGroupCount > 0);
      empty.hidden = hasResults;
    }
  }

  function wireCopyButtons(page, strings) {
    var buttons = page.querySelectorAll("[data-roxyapi-copy]");
    buttons.forEach(function (button) {
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
    }, COPY_FLASH_MS);
  }

  function wireKeyboardShortcut(page) {
    var input = page.querySelector(".roxyapi-library-search");
    if (!input) {
      return;
    }
    document.addEventListener("keydown", function (event) {
      if (event.key !== "/") {
        return;
      }
      var target = event.target;
      if (!target) {
        return;
      }
      var tag = (target.tagName || "").toLowerCase();
      if (
        tag === "input" ||
        tag === "textarea" ||
        tag === "select" ||
        target.isContentEditable
      ) {
        return;
      }
      event.preventDefault();
      input.focus();
      input.select();
    });
  }
})();
