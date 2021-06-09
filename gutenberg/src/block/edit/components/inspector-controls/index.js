import './editor.scss';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button } = wp.components;
const { InspectorControls } = !!wp.blockEditor ? wp.blockEditor : wp.editor;

export default class FooGalleryEditInspectorControls extends Component {
	render(){
		const { select, onRequestModalOpen, canEdit, edit, onRequestGalleryEdit, children } = this.props;
		return (
				<InspectorControls>
					<div className="foogallery-inspector-controls__button-container">
						{ canEdit ? <Button isSecondary onClick={ onRequestGalleryEdit } icon="edit" label={ edit }/> : null }&nbsp;
						<Button isPrimary onClick={ onRequestModalOpen }>{ select }</Button>
					</div>
					{ children }
				</InspectorControls>
		);
	}
}

FooGalleryEditInspectorControls.defaultProps = {
	canEdit: false,
	edit: __("Edit Gallery", "foogallery"),
	select: __("Select Gallery", "foogallery"),
	onRequestGalleryEdit: _.noop,
	onRequestModalOpen: _.noop
};