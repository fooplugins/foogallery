import FooGalleryModal from '../modal';
import FooGalleryPlaceholder from '../placeholder';
import FooGalleryEditorBlockControls from './block-controls';
import FooGalleryEditorInspectorControls from './inspector-controls';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { Button } = wp.components;

export default class FooGalleryEditorDuplicate extends Component {
	render(){
		const { icon, label, instructions, button, ...props } = this.props;

		let placeholderProps = { icon, label, instructions };

		return (
				<Fragment>
					<FooGalleryEditorBlockControls { ...props } />
					<FooGalleryPlaceholder { ...placeholderProps }>
						<Button isDefault isLarge onClick={ props.onRequestModalOpen }>{ button }</Button>
					</FooGalleryPlaceholder>
					<FooGalleryModal { ...props } />
					<FooGalleryEditorInspectorControls { ...props } />
				</Fragment>
		);
	}
}

FooGalleryEditorDuplicate.defaultProps = {
	icon: "format-gallery",
	label: __("FooGallery", "foogallery"),
	instructions: __("Duplicate gallery, please select another to insert.", "foogallery"),
	button: __("Select Gallery", "foogallery"),
	onRequestModalOpen: _.noop
};