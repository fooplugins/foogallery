import classnames from 'classnames';

const { Dashicon } = wp.components;
const { Component } = wp.element;

export default class FooGalleryModalItem extends Component {
	render(){
		const { data, className, isSelected, isDisabled, onSelected } = this.props;
		let props = {
			className: classnames( "foogallery-modal__item", className, {
				"is-selected": isSelected,
				"is-disabled": isDisabled
			})
		};
		if ( !isDisabled ){
			let selectable = {
				onClick: ( event ) => {
					event.stopPropagation();
					onSelected( data.id );
				},
				onKeyPress: ( event ) => {
					event.stopPropagation();
					if (event.which == 32 || event.which == 13) {
						onSelected( data.id );
					}
				},
				tabIndex: 0
			};
			props = { ...props, ...selectable };
		}
		let thumb = !!data.thumbnail
				? (<img className="foogallery-modal__item-thumbnail" src={data.thumbnail} />)
				: (<Dashicon className="foogallery-modal__item-thumbnail" icon="format-image"/>);

		return (
				<figure { ...props }>
					{ thumb }
					<figcaption className="foogallery-modal__item-caption">
						{ data.name }
					</figcaption>
					{ isSelected ? <Dashicon className="foogallery-modal__icon-selected" icon="yes"/> : null }
					{ isDisabled ? <Dashicon className="foogallery-modal__icon-disabled" icon="no"/> : null }
				</figure>
		);
	}
}

FooGalleryModalItem.defaultProps = {
	data: {},
	className: "",
	isSelected: false,
	isDisabled: false,
	onSelected: _.noop
};