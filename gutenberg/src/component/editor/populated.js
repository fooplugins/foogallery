import FooGalleryModal from '../modal';
import FooGalleryServerSideRender from '../server-side-render';
import FooGalleryEditorBlockControls from './block-controls';
import FooGalleryEditorInspectorControls from './inspector-controls';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;

export default class FooGalleryEditorPopulated extends Component {
	render(){
		const { block, attributes, reload, ...props } = this.props;
		return (
				<Fragment>
					<FooGalleryEditorBlockControls { ...props } />
					<FooGalleryServerSideRender
							block={ block }
							attributes={ attributes }
							reload={ reload }
					/>
					<FooGalleryModal { ...props } />
					<FooGalleryEditorInspectorControls { ...props } />
				</Fragment>
		);
	}
}

FooGalleryEditorPopulated.defaultProps = {
	block: "fooplugins/foogallery",
	attributes: {},
	canEdit: true,
	canReload: true,
	isModalOpen: false,
	onRequestModalOpen: _.noop,
	onRequestModalClose: _.noop,
	onRequestGalleryInsert: _.noop,
	onRequestGalleryEdit: _.noop,
	onRequestGalleryReload: _.noop,
	onRequestBlockRemove: _.noop
};