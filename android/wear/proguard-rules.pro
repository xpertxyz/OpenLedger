# ProtoLayout builds a tile's layout as protobuf, and the tile is deserialised in the system
# launcher's process rather than ours. R8 has no call graph reaching those classes and strips
# them: the build succeeds, and the tile renders as a blank card with nothing in the log.
-keep class androidx.wear.protolayout.** { *; }
-keep class androidx.wear.tiles.** { *; }

# The tile service is named in AndroidManifest.xml and instantiated by the system.
-keep class com.xpertxyz.ledger.wear.LedgerTileService { *; }
