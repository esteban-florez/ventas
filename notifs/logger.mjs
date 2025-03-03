import pino from 'pino'

let store = {
  log: null
}

export function logger() {
  if (store.log) {
    return store.log
  }
  
  store.log = pino({
    transport: {
      targets: [
        {
          target: 'pino-pretty',
          options: {
            colorize: true,
            translateTime: 'SYS:standard',
          },
        },
        {
          target: 'pino/file',
          options: {
            destination: './notifs.log',
          },
        },
      ],
    },
    customLevels: {
      status: 35,
    },
    level: 'status',
  })

  return store.log
}

