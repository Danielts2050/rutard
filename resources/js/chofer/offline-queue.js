import { openDB } from 'idb';

const DB_NAME = 'ruta-transporte';
const DB_VERSION = 1;

let dbPromise;

export async function initDB() {
    dbPromise = openDB(DB_NAME, DB_VERSION, {
        upgrade(db) {
            if (!db.objectStoreNames.contains('ubicaciones')) {
                db.createObjectStore('ubicaciones', { keyPath: 'id', autoIncrement: true });
            }
            if (!db.objectStoreNames.contains('rutas')) {
                db.createObjectStore('rutas', { keyPath: 'id', autoIncrement: true });
            }
        },
    });
    return dbPromise;
}

export async function saveUbicacion(ubicacion) {
    const db = await dbPromise;
    return db.add('ubicaciones', { ...ubicacion, synced: false, created_at: new Date().toISOString() });
}

export async function getPendingUbicaciones() {
    const db = await dbPromise;
    return db.getAllFromIndex('ubicaciones', 'by_synced', 0);
}

export async function markSynced(id) {
    const db = await dbPromise;
    const tx = db.transaction('ubicaciones', 'readwrite');
    const item = await tx.store.get(id);
    if (item) {
        item.synced = true;
        await tx.store.put(item);
    }
    await tx.done;
}

export async function saveRuta(ruta) {
    const db = await dbPromise;
    return db.add('rutas', ruta);
}
