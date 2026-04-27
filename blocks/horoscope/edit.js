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

  const effectiveSign =
    attributes.sign || (context && context["roxyapi/sign"]) || "aries";

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title={__("Horoscope Settings", "roxyapi")}>
          <SelectControl
            label={__("Zodiac Sign", "roxyapi")}
            value={effectiveSign}
            options={SIGNS}
            onChange={(sign) => setAttributes({ sign })}
          />
          <SelectControl
            label={__("Period", "roxyapi")}
            value={attributes.period}
            options={PERIODS}
            onChange={(period) => setAttributes({ period })}
          />
        </PanelBody>
      </InspectorControls>
      <ServerSideRender
        block="roxyapi/horoscope"
        attributes={{ ...attributes, sign: effectiveSign }}
      />
    </div>
  );
}
