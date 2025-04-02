export function getNewProductMessage(producto) {
  const { OWNER_NAME } = process.env
  const precio = Number(producto.precioVenta).toFixed(2)

  return `*${OWNER_NAME}*\n\nEstimado/a cliente, le notificamos que el producto *"${producto.nombre}"* ha llegado a nuestra tienda el día de hoy, a un precio de venta de *$${precio}* por ${producto.unidad}.`
}
