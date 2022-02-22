Nova.booting((Vue, router, store) => {
  window.Vue = Vue
  router.addRoutes([
    {
      name: 'liqpay',
      path: '/liqpay',
      component: require('./components/Tool').default,
    },
  ])
})
