export const FEATURES = {
  enableMultiOrg: import.meta.env.VITE_ENABLE_MULTI_ORG === 'true',
  enableCashPayments: import.meta.env.VITE_ENABLE_CASH_PAYMENTS === 'true',
  betaFeatures: import.meta.env.VITE_BETA_FEATURES === 'true'
}
