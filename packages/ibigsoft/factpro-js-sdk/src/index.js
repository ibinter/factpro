/**
 * @ibigsoft/factpro-sdk — Point d'entrée principal
 *
 * @example
 * import { FactProClient, AuthError, ValidationError } from '@ibigsoft/factpro-sdk';
 */

export { FactProClient } from './FactProClient.js';
export { DocumentResource } from './resources/DocumentResource.js';
export { CustomerResource } from './resources/CustomerResource.js';
export { ProductResource } from './resources/ProductResource.js';
export { InvoiceResource } from './resources/InvoiceResource.js';
export { FactProError } from './errors/FactProError.js';
export { AuthError } from './errors/AuthError.js';
export { ValidationError } from './errors/ValidationError.js';
