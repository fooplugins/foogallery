import {
	FooGalleryEditModal,
	FooGalleryEditPlaceholder,
	FooGalleryEditBlockControls,
	FooGalleryEditInspectorControls
} from '../components';

import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Component, Fragment } from '@wordpress/element';

export default class FooGalleryEditEmpty extends Component {
	render(){
		const { icon, label, instructions, button, ...props } = this.props;

		let placeholderProps = { icon, label, instructions };

		return (
				<Fragment>
					<FooGalleryEditBlockControls { ...props } />
					<FooGalleryEditPlaceholder { ...placeholderProps }>
						<Button isSecondary onClick={ props.onRequestModalOpen }>{ button }</Button>
					</FooGalleryEditPlaceholder>
					<FooGalleryEditModal { ...props } />
					<FooGalleryEditInspectorControls { ...props }/>
				</Fragment>
		);
	}
}

FooGalleryEditEmpty.defaultProps = {
	icon: "format-gallery",
	label: __("FooGallery", "foogallery"),
	instructions: __("Select the gallery you want to insert.", "foogallery"),
	button: __("Select Gallery", "foogallery"),
	onRequestModalOpen: _.noop
};