//
//  TellMeApp.swift
//  TellMe
//
//  Created by Antonio Blanco Oliva on 8/2/24.
//

import SwiftUI

@main
struct TellMeApp: App {
    var body: some Scene {
        WindowGroup {
            ContentView(audioRecorder: AudioRecorder())
        }
    }
}
