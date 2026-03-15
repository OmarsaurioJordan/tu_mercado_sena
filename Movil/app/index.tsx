import { meService } from "@/services/authService";
import { deleteToken, getToken } from "@/src/lib/authToken";
import { useRouter } from "expo-router";
import React, { useEffect } from "react";
import { ActivityIndicator, View } from "react-native";

export default function Index() {
  const router = useRouter();

  useEffect(() => {
    const bootstrap = async () => {
      const token = await getToken();

      if (!token) {
        router.replace("/(stack)/welcome");
        return;
      }

      try {
        // Validar token con backend
        await meService();

        router.replace("/(tabs)/Home");
      } catch (error) {
        // Token inválido / expirado
        await deleteToken();
        router.replace("/(stack)/welcome");
      }
    };

    bootstrap();
  }, []);

  return (
    <View style={{ flex: 1, alignItems: "center", justifyContent: "center" }}>
      <ActivityIndicator size="large" />
    </View>
  );
}
