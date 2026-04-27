import { useBlockProps } from "@wordpress/block-editor";
import { Placeholder } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  return (
    <div {...useBlockProps()}>
      <Placeholder
        icon="calculator"
        label={__("Numerology", "roxyapi")}
        instructions={__(
          "Life path, expression, soul urge, and personal year numbers.",
          "roxyapi",
        )}
      />
    </div>
  );
}
