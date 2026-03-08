import { Ionicons } from "@expo/vector-icons";
import React, { useMemo, useRef, useState } from "react";
import {
  Platform,
  Pressable,
  StyleProp,
  StyleSheet,
  TextInput,
  TextInputProps,
  View,
  ViewStyle,
} from "react-native";

interface Props extends Omit<TextInputProps, "onChangeText" | "value"> {
  type?: "text" | "password" | "email" | "number" | "string";
  className?: string;
  placeholder?: string;
  value?: string;
  placeholderTextColor?: string;
  onChangeText?: (text: string) => void;
  icon?: React.ReactNode;
  showPasswordToggle?: boolean;

  containerStyle?: StyleProp<ViewStyle>;

  containerClassName?: string;
}

const CustomInput = ({
  type = "text",
  className,
  containerClassName,
  containerStyle,
  placeholder,
  value = "",
  placeholderTextColor = "#CDCDCD",
  onChangeText,
  icon,
  showPasswordToggle = true,
  ...rest
}: Props) => {
  const isPassword = type === "password";
  const isNumber = type === "number";
  const isEmail = type === "email";

  const [showPassword, setShowPassword] = useState(false);

  const inputRef = useRef<TextInput>(null);
  const lastGoodValueRef = useRef<string>(value ?? "");
  const lastKeyRef = useRef<string | null>(null);

  const keyboardType = useMemo(() => {
    if (isNumber) return "numeric";
    if (isEmail) return "email-address";
    return "default";
  }, [isNumber, isEmail]);

  const autoCapitalize = isEmail ? "none" : rest.autoCapitalize ?? "sentences";

  const handleKeyPress: TextInputProps["onKeyPress"] = (e) => {
    lastKeyRef.current = e.nativeEvent.key;
    rest.onKeyPress?.(e);
  };

  const handleChangeText = (t: string) => {
    if (Platform.OS === "ios" && isPassword) {
      const prev = lastGoodValueRef.current;

      // Fix de backspace iOS (bug conocido en secureTextEntry)
      if (t === "" && prev.length > 1 && lastKeyRef.current === "Backspace") {
        const fixed = prev.slice(0, -1);

        inputRef.current?.setNativeProps({ text: fixed });

        lastGoodValueRef.current = fixed;
        onChangeText?.(fixed);
        return;
      }

      lastGoodValueRef.current = t;
      onChangeText?.(t);
      return;
    }

    lastGoodValueRef.current = t;
    onChangeText?.(t);
  };

  return (
    <View
      className={`flex-row items-center rounded-full bg-[#F5F5F7] px-4 ${
        className ?? ""
      } ${containerClassName ?? ""}`}
      style={containerStyle}
    >
      {icon && <View className="mr-2">{icon}</View>}

      <TextInput
        ref={inputRef}
        value={value ?? ""}
        onKeyPress={handleKeyPress}
        onChangeText={handleChangeText}
        placeholder={placeholder}
        placeholderTextColor={placeholderTextColor}
        secureTextEntry={isPassword ? !showPassword : false}
        keyboardType={keyboardType}
        autoCapitalize={autoCapitalize}
        autoCorrect={false}
        spellCheck={false}
        textContentType={
          isPassword ? "none" : isEmail ? "emailAddress" : rest.textContentType
        }
        autoComplete={isPassword ? "off" : isEmail ? "email" : rest.autoComplete}
        style={styles.input}
        {...rest}
      />

      {isPassword && showPasswordToggle && (
        <Pressable
          onPress={() => setShowPassword((p) => !p)}
          hitSlop={10}
          style={styles.eyeBtn}
        >
          <Ionicons
            name={showPassword ? "eye-outline" : "eye-off-outline"}
            size={20}
            color="#9CA3AF"
          />
        </Pressable>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  input: { flex: 1, fontSize: 16, color: "#1a202a", paddingVertical: 12 },
  eyeBtn: { paddingLeft: 10, paddingVertical: 6 },
});

export default CustomInput;