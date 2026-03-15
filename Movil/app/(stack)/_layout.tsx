import { Stack } from "expo-router";
import React from "react";

export default function StackLayout() {
  return (
    <Stack
      screenOptions={{
        headerShown: false
      }}
    >
      <Stack.Screen name="login/index" />

      <Stack.Screen 
        name="register/index" 
        options={{ title: "Registrarse" }} 
      />

      <Stack.Screen
      name="verify/index"
      options={{ title: "Verificar" }}
      />

      {/* <Stack.Screen 
        name="resetPassword/index" 
        options={{ title: "Restablecer Contraseña" }} 
      /> */}
    </Stack>
  );
}
