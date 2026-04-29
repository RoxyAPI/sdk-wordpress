/*
 * Horoscope block editor component.
 *
 * SECURITY: the editor preview must run server side via ServerSideRender
 * or our custom REST preview route. Do NOT refactor to a fetch that returns
 * the upstream RoxyAPI key or hits roxyapi.com from the browser.
 */

import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  SelectControl,
  Placeholder,
  Button,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";
import { __ } from "@wordpress/i18n";

const SIGNS = [
  { label: __("Pick your zodiac sign", "roxyapi"), value: "" },
  { label: "Aries", value: "aries" },
  { label: "Taurus", value: "taurus" },
  { label: "Gemini", value: "gemini" },
  { label: "Cancer", value: "cancer" },
  { label: "Leo", value: "leo" },
  { label: "Virgo", value: "virgo" },
  { label: "Libra", value: "libra" },
  { label: "Scorpio", value: "scorpio" },
  { label: "Sagittarius", value: "sagittarius" },
  { label: "Capricorn", value: "capricorn" },
  { label: "Aquarius", value: "aquarius" },
  { label: "Pisces", value: "pisces" },
];

const PERIODS = [
  { label: "Daily", value: "daily" },
  { label: "Weekly", value: "weekly" },
  { label: "Monthly", value: "monthly" },
];

export default function Edit({ attributes, setAttributes, context }) {
  const blockProps = useBlockProps();
  const hasKey = window.RoxyAPIEditor && window.RoxyAPIEditor.hasKey;
  const settingsUrl =
    (window.RoxyAPIEditor && window.RoxyAPIEditor.settingsUrl) || "#";

  if (!hasKey) {
    return (
      <div {...blockProps}>
        <Placeholder
          icon="star-filled"
          label={__("RoxyAPI not connected", "roxyapi")}
          instructions={__(
            "Add your RoxyAPI key in Settings to use this block.",
            "roxyapi",
          )}
        >
          <Button variant="primary" href={settingsUrl}>
            {__("Open settings", "roxyapi")}
          </Button>
        </Placeholder>
      </div>
    );
  }

  // Empty sign + no inherited Astrology Section context = block is
  // unconfigured. Show a placeholder instead of falling back to "aries"
  // so the editor doesn't read as "this plugin only knows Aries" and
  // we don't burn an API call on a value the site owner never picked.
  // The site owner can either pick a sign (becomes static) or leave it
  // blank (front end renders the visitor sign-picker form).
  const effectiveSign =
    attributes.sign || (context && context["roxyapi/sign"]) || "";

  const inspector = (
    <InspectorControls>
      <PanelBody title={__("Horoscope Settings", "roxyapi")}>
        <SelectControl
          label={__("Zodiac Sign", "roxyapi")}
          value={effectiveSign}
          options={SIGNS}
          onChange={(sign) => setAttributes({ sign })}
          help={__(
            "Leave blank to render a visitor sign picker on the front end.",
            "roxyapi",
          )}
        />
        <SelectControl
          label={__("Period", "roxyapi")}
          value={attributes.period}
          options={PERIODS}
          onChange={(period) => setAttributes({ period })}
        />
      </PanelBody>
    </InspectorControls>
  );

  if (effectiveSign === "") {
    return (
      <div {...blockProps}>
        {inspector}
        <Placeholder
          icon="star-filled"
          label={__("Horoscope", "roxyapi")}
          instructions={__(
            "Pick a zodiac sign in the sidebar to preview the daily reading, or publish as is to render a visitor sign picker on the front end.",
            "roxyapi",
          )}
        />
      </div>
    );
  }

  return (
    <div {...blockProps}>
      {inspector}
      <ServerSideRender
        block="roxyapi/horoscope"
        attributes={{ ...attributes, sign: effectiveSign }}
      />
    </div>
  );
}
