import { useBlockProps } from "@wordpress/block-editor";
import { Placeholder } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  return (
    <div {...useBlockProps()}>
      <Placeholder
        icon="book"
        label={__("I Ching", "roxyapi")}
        instructions={__(
          "I Ching hexagram cast and interpretation.",
          "roxyapi",
        )}
      />
    </div>
  );
}
