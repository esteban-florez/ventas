const { VUE_APP_API_URL: RUTA_GLOBAL } = process.env

const HttpService =  {
	async registrar(ruta, datos) {
		let respuesta = await fetch(`${RUTA_GLOBAL}/${ruta}`, {
			method: "post",
			body: JSON.stringify(datos),
      credentials: 'include',
		});
		let resultado = await respuesta.json()
		return resultado
	},

	async obtener(ruta) {
		let respuesta = await fetch(`${RUTA_GLOBAL}/${ruta}`, { credentials: 'include' })
		let resultado = await respuesta.json()
		return resultado
	},

	async editar(ruta, datos) {
		let respuesta = await fetch(`${RUTA_GLOBAL}/${ruta}`, {
			method: "post",				
			body: JSON.stringify(datos),
      credentials: 'include',
		});
		let resultado = await respuesta.json()
		return resultado
	},

	async eliminar(ruta, datos) {
		let respuesta = await fetch(`${RUTA_GLOBAL}/${ruta}`, {
			method: "post",				
			body: JSON.stringify(datos),
      credentials: 'include',
		});
		let resultado = await respuesta.json()
		return resultado
	},

	async obtenerConConsultas(ruta, payload){
		let respuesta = await fetch(`${RUTA_GLOBAL}/${ruta}`, {
			method: "post",				
			body: JSON.stringify(payload),
      credentials: 'include',
		});
		let resultado = await respuesta.json()
		return resultado
	},
}

export default HttpService
