import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { useState } from "react";
import { Alert, ScrollView, Text, TextInput, TouchableOpacity, View } from "react-native";

export default function CambioContra() {
  const router = useRouter();
  const [actual, setActual] = useState("");
  const [nueva, setNueva] = useState("");
  const [confirmar, setConfirmar] = useState("");

  const validarPassword = () => {
    if (!actual || !nueva || !confirmar) {
      Alert.alert("Error", "Todos los campos son obligatorios");
      return;
    }
    if (nueva.length < 6) {
      Alert.alert("Error", "La contraseña debe tener al menos 6 caracteres");
      return;
    }
    if (nueva !== confirmar) {
      Alert.alert("Error", "Las contraseñas no coinciden");
      return;
    }
    Alert.alert("Éxito", "Contraseña cambiada correctamente");
  };

  return (
    <View style={{ flex: 1, backgroundColor: "#ffffff" }}>
      <ScrollView contentContainerStyle={{ padding: 24, paddingBottom: 60 }}>

        {/* HEADER */}
        <View className="flex-row items-center mt-6 mb-8">
          <TouchableOpacity onPress={() => router.back()}>
            <Ionicons name="arrow-back" size={26} color="black" />
          </TouchableOpacity>

          <Text className="text-black text-xl font-bold ml-4 flex-1 text-center">
            Cambio de Contraseña
          </Text>
        </View>

        {/* DESCRIPCIÓN */}
        <Text className="text-black text-base mb-6 leading-6">
          La contraseña debe tener al menos 6 caracteres e incluir una combinación
          de número, letras y caracteres especiales (!$@%)
        </Text>

        {/* INPUTS */}
        <Text className="text-black mb-2">Contraseña actual</Text>
        <TextInput
          value={actual}
          onChangeText={setActual}
          placeholder="Ingrese su contraseña actual"
          placeholderTextColor="#555"
          secureTextEntry
          className="bg-gray-100 rounded-full px-4 py-3 mb-6"
        />

        <Text className="text-black mb-2">Contraseña nueva</Text>
        <TextInput
          value={nueva}
          onChangeText={setNueva}
          placeholder="Ingrese su contraseña nueva"
          placeholderTextColor="#555"
          secureTextEntry
          className="bg-gray-100 rounded-full px-4 py-3 mb-6"
        />

        <Text className="text-black mb-2">Confirmar contraseña</Text>
        <TextInput
          value={confirmar}
          onChangeText={setConfirmar}
          placeholder="Confirme su contraseña nueva"
          placeholderTextColor="#555"
          secureTextEntry
          className="bg-gray-100 rounded-full px-4 py-3 mb-8"
        />

        {/* BOTÓN */}
        <TouchableOpacity
          onPress={validarPassword}
          className="bg-green-600 py-4 rounded-full items-center"
        >
          <Text className="text-white font-bold text-lg">
            Cambiar contraseña
          </Text>
        </TouchableOpacity>

      </ScrollView>
    </View>
  );
}