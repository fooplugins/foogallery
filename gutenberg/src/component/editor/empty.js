import FooGalleryModal from '../modal';
import FooGalleryPlaceholder from '../placeholder';
import FooGalleryEditorBlockControls from './block-controls';
import FooGalleryEditorInspectorControls from './inspector-controls';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { Button } = wp.components;

export default class FooGalleryEditorEmpty extends Component {
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
					<FooGalleryEditorInspectorControls { ...props }/>
				</Fragment>
		);
	}
}

FooGalleryEditorEmpty.defaultProps = {
	icon: "format-gallery",
	label: __("FooGallery", "foogallery"),
	instructions: __("Select the gallery you want to insert.", "foogallery"),
	button: __("Select Gallery", "foogallery"),
	onRequestModalOpen: _.noop
};