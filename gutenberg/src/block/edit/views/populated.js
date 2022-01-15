import {
	FooGalleryEditModal,
	FooGalleryEditServerSideRender,
	FooGalleryEditBlockControls,
	FooGalleryEditInspectorControls
} from '../components';

const { Component, Fragment } = wp.element;

export default class FooGalleryEditPopulated extends Component {
	render(){
		const { block, attributes, reload, ...props } = this.props;
		return (
			<Fragment>
				<FooGalleryEditBlockControls { ...props } />
				<FooGalleryEditServerSideRender
						block={ block }
						attributes={ attributes }
						reload={ reload }
				/>
				<FooGalleryEditModal { ...props } />
				<FooGalleryEditInspectorControls { ...props } />
			</Fragment>
		);
	}
}

FooGalleryEditPopulated.defaultProps = {
	block: "fooplugins/foogallery",
	attributes: {},
	reload: false,
	canEdit: true,
	canReload: true
};