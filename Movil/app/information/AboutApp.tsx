import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { ScrollView, Text, TouchableOpacity, View } from "react-native";

export default function AboutApp() {
  const router = useRouter();

  return (
    <ScrollView className="flex-1 bg-[#F3F4F6] px-5 pt-12">
      {/* HEADER */}
      <View className="flex-row items-center mb-8">
        <TouchableOpacity onPress={() => router.back()} className="mr-3">
          <Ionicons name="arrow-back" size={26} color="black" />
        </TouchableOpacity>
        <Text className="text-xl font-bold flex-1 text-center">
          Acerca de la aplicación
        </Text>
      </View>

      {/* CARD */}
      <View className="bg-white rounded-2xl p-5 shadow">
        <Text className="text-base text-gray-700 mb-3">
          Esta aplicación fue desarrollada utilizando React Native y Expo,
          enfocándose en rendimiento y diseño moderno.
        </Text>

        <Text className="text-base text-gray-700">
          Versión actual: 1.0.0
        </Text>
      </View>
    </ScrollView>
  );
}