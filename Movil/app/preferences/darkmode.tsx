import { Ionicons } from "@expo/vector-icons";
import { useState } from "react";
import { ScrollView, Switch, Text, TouchableOpacity, View } from "react-native";
import { useRouter } from "expo-router";

export default function ModoOscuro() {
  const router = useRouter();
  const [darkMode, setDarkMode] = useState(false);

  return (
    <View style={{ flex: 1, backgroundColor: "#D1D5DB" }}>
      <ScrollView contentContainerStyle={{ padding: 20, paddingBottom: 40 }}>

        {/* HEADER */}
        <View className="flex-row items-center mt-6 mb-6">
          <TouchableOpacity onPress={() => router.back()}>
            <Ionicons name="arrow-back" size={26} color="black" />
          </TouchableOpacity>

          <Text className="text-black text-xl font-bold ml-4 flex-1 text-center">
            Modo oscuro
          </Text>
        </View>

        {/* TARJETA */}
        <View className="bg-white rounded-2xl p-4 shadow">

          <View className="flex-row justify-between items-center">
            <View className="flex-1 pr-4">
              <Text className="text-base font-semibold text-gray-900">
                Activar modo oscuro
              </Text>
              <Text className="text-sm text-gray-500">
                Cambia la apariencia de la aplicación
              </Text>
            </View>

            <Switch
              value={darkMode}
              onValueChange={setDarkMode}
              trackColor={{ false: "#CBD5F5", true: "#2563EB" }}
              thumbColor={darkMode ? "#FFFFFF" : "#F4F4F4"}
            />
          </View>

        </View>

        {/* TEXTO INFERIOR */}
        <Text className="text-gray-600 text-sm mt-6">
          Al activar el modo oscuro, se reducirá el brillo de la interfaz
          para una experiencia más cómoda.
        </Text>

      </ScrollView>
    </View>
  );
}