<?php
namespace Neon;

enum WorkMode: string
{
    case Stationary = 'stationary';
    case Hybrid = 'hybrid';
    case FullyRemote = 'fullyRemote';
}
