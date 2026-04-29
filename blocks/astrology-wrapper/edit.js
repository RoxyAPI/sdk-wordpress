import {
  useBlockProps,
  useInnerBlocksProps,
  InspectorControls,
} from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

const SIGNS = [
  { label: __("No default (each child block decides)", "roxyapi"), value: "" },
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

const ALLOWED_BLOCKS = [
  "roxyapi/horoscope",
  "roxyapi/natal-chart",
  "roxyapi/tarot",
  "roxyapi/numerology",
  "roxyapi/iching",
  "roxyapi/dreams",
  "roxyapi/biorhythm",
  "roxyapi/angel-number",
  "roxyapi/crystal",
];

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    allowedBlocks: ALLOWED_BLOCKS,
    template: [["roxyapi/horoscope"]],
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Default values", "roxyapi")}>
          <SelectControl
            label={__("Default Sign", "roxyapi")}
            value={attributes.defaultSign}
            options={SIGNS}
            onChange={(defaultSign) => setAttributes({ defaultSign })}
          />
          <TextControl
            label={__("Default Birth Date", "roxyapi")}
            help={__("YYYY-MM-DD format", "roxyapi")}
            value={attributes.defaultBirthDate}
            onChange={(defaultBirthDate) => setAttributes({ defaultBirthDate })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...innerBlocksProps} />
    </>
  );
}
