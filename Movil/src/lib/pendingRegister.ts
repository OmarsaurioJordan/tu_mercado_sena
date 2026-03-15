import * as SecureStore from "expo-secure-store";

const KEY = "pending_register";

export async function savePendingRegister(payload: {
  email: string;
  cuenta_id: number;
  datosEncriptados: string;
}) {
  await SecureStore.setItemAsync(KEY, JSON.stringify(payload));
}

export async function getPendingRegister() {
  const raw = await SecureStore.getItemAsync(KEY);
  return raw ? JSON.parse(raw) as { email: string; cuenta_id: number; datosEncriptados: string } : null;
}

export async function clearPendingRegister() {
  await SecureStore.deleteItemAsync(KEY);
}
