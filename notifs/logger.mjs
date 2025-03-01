import pino from 'pino'

export const options = {
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
}

export const log = pino(options)
