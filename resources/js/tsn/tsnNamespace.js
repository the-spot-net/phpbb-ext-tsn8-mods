/**
 * Creates a unique string that can be used as a namespace for events
 * @param objectName
 * @returns {string}
 */
export default function tsnNamespace({ objectName = '' } = {}) {
  const rand = Math.random().toString().replace('.', '');

  return `${objectName}.${rand}`;
}
