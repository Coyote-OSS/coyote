<?php
namespace Neon2\JobBoard;

enum WorkMode: string {
    case Stationary = 'stationary';
    case Hybrid = 'hybrid';
    case FullyRemote = 'fullyRemote';
}
