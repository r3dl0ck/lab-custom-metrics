package com.example.shop;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

/** Endpoint do wywoływania błędów 500. */
@RestController
public class CrashController {

    @GetMapping("/crash")
    public String crash() {
        throw new IllegalStateException("symulowany błąd aplikacji");
    }
}
