const rendered = [];
export default class FooGalleryEditRendered {
	static get array(){
		return rendered;
	}

	static add( id, clientId ){
		let index = rendered.findIndex(r => r.id == id);
		if (index === -1){
			rendered.push({ id, clientId });
			return true;
		}
		return false;
	}

	static remove( clientId ){
		let index = rendered.findIndex(r => r.clientId == clientId);
		if (index !== -1){
			rendered.splice(index, 1);
			return true;
		}
		return index === -1 || false;
	}

	static update( id, clientId ){
		if (this.remove( clientId )){
			return this.add( id, clientId );
		}
		return false;
	}

	static ids(){
		return rendered.map(r => r.id);
	}

	static contains( id, clientId ){
		return rendered.findIndex(r => r.id == id && r.clientId != clientId) !== -1;
	}
}
